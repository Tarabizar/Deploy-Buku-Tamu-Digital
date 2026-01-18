<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php'; // Pastikan path ini benar
require_once __DIR__ . '/../koneksi/csrf.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page_title = "Data Survei Kepuasan Layanan";
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

$csrf_delete_kepuasan_token = csrf_generate_token('delete_kepuasan');

//======================================================================
// DEFINISI FUNGSI DISPLAY RATING (HANYA SATU KALI DI SINI)
//======================================================================
if (!function_exists('generate_star_display')) { // Menggunakan nama yang berbeda dan pengecekan
    function generate_star_display($rating_value, $max_stars = 5) {
        $stars_html = '';
        for ($i = 1; $i <= $max_stars; $i++) {
            $stars_html .= ($i <= $rating_value) ? '<i class="bi bi-star-fill rating-stars"></i>' : '<i class="bi bi-star rating-stars"></i>';
        }
        return $stars_html . " <span class='small text-muted'>(" . intval($rating_value) . "/" . $max_stars . ")</span>";
    }
}
//======================================================================

// Handle Aksi Hapus (jika ada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    if (!csrf_validate_token($_POST['csrf_token'] ?? '', 'delete_kepuasan')) {
        $_SESSION['message'] = "Permintaan tidak valid. Silakan coba lagi.";
        $_SESSION['message_type'] = "danger";
        header("Location: data_kepuasan.php");
        exit;
    }

    $id_kepuasan_to_delete = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if ($id_kepuasan_to_delete) {
        $sql_delete = "DELETE FROM tb_kepuasan WHERE id_kepuasan = ?";
        if ($stmt_delete = $koneksi->prepare($sql_delete)) {
            $stmt_delete->bind_param("i", $id_kepuasan_to_delete);
            if ($stmt_delete->execute()) {
                $_SESSION['message'] = "Data survei kepuasan berhasil dihapus.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal menghapus data survei: " . $stmt_delete->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt_delete->close();
        } else {
            $_SESSION['message'] = "Gagal menyiapkan statement hapus survei: " . $koneksi->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: data_kepuasan.php");
        exit;
    } else {
        $_SESSION['message'] = "ID survei tidak valid untuk dihapus.";
        $_SESSION['message_type'] = "danger";
        header("Location: data_kepuasan.php");
        exit;
    }
}

// Fetch semua data survei kepuasan
$survei_list = [];
$sql_select_survei = "SELECT id_kepuasan, id_tamu_fk, nama_responden, tanggal_survei, waktu_survei, 
                             nilai_pelayanan, nilai_fasilitas, nilai_keramahan, nilai_kecepatan, saran_masukan 
                      FROM tb_kepuasan 
                      ORDER BY tanggal_survei DESC, waktu_survei DESC";
$result_survei = $koneksi->query($sql_select_survei);
if ($result_survei && $result_survei->num_rows > 0) {
    while ($row = $result_survei->fetch_assoc()) {
        $survei_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<?php include '_partials/head.php'; ?>
<style>
    .table-responsive { margin-top: 1rem; }
    .action-buttons .btn { margin-right: 5px; }
    .rating-stars { color: #ffc107; } 
    .saran-preview { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
<body>
    <?php
    if (file_exists(__DIR__ . '/_partials/navbar.php')) { include_once __DIR__ . '/_partials/navbar.php'; }
    if (file_exists(__DIR__ . '/_partials/sidebar.php')) { include_once __DIR__ . '/_partials/sidebar.php'; }
    ?>
    <main class="main-content">
        <div class="container-fluid">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="export_kepuasan.php" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel-fill"></i> Ekspor ke Excel (XLS)
        </a>
    </div>
</div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : ($message_type === 'danger' ? 'danger' : 'info'); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary-gradient text-white">
                    <h5 class="mb-0">Daftar Survei Kepuasan</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($survei_list)): ?>
                    <div class="table-responsive">
                        <table id="tabelDataSurvei" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal</th>
                                    <th>Responden</th>
                                    <th>Pelayanan</th>
                                    <th>Fasilitas</th>
                                    <th>Keramahan</th>
                                    <th>Kecepatan</th>
                                    <th>Saran (Singkat)</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $nomor = 1; foreach ($survei_list as $survei): ?>
                                <tr>
                                    <td><?php echo $nomor++; ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($survei['tanggal_survei'] . ' ' . $survei['waktu_survei']))); ?></td>
                                    <td><?php echo htmlspecialchars($survei['nama_responden'] ?: ($survei['id_tamu_fk'] ? 'Tamu ID: '.$survei['id_tamu_fk'] : 'Anonim')); ?></td>
                                    <td><?php echo generate_star_display($survei['nilai_pelayanan']); // Panggil fungsi yang sudah didefinisikan di atas ?></td>
                                    <td><?php echo generate_star_display($survei['nilai_fasilitas']); ?></td>
                                    <td><?php echo generate_star_display($survei['nilai_keramahan']); ?></td>
                                    <td><?php echo generate_star_display($survei['nilai_kecepatan']); ?></td>
                                    <td class="saran-preview" title="<?php echo htmlspecialchars($survei['saran_masukan']); ?>">
                                        <?php echo htmlspecialchars(mb_strimwidth($survei['saran_masukan'], 0, 50, "...")); ?>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="detail_kepuasan.php?id=<?php echo $survei['id_kepuasan']; ?>" class="btn btn-sm btn-info" title="Lihat Detail Survei"><i class="bi bi-eye-fill"></i></a>
                                        <form method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data survei ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $survei['id_kepuasan']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_delete_kepuasan_token, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Survei">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Belum ada data survei kepuasan yang tersimpan.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tabelDataSurvei').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                "pageLength": 10,
                "order": [[ 1, "desc" ]] 
            });
        });

        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const adminSidebar = document.getElementById('adminSidebar');
        if (sidebarToggleBtn && adminSidebar) {
            sidebarToggleBtn.addEventListener('click', function() {
                adminSidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>
