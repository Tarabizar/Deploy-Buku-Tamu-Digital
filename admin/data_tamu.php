<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php'; // Sesuaikan path jika berbeda
require_once __DIR__ . '/../koneksi/csrf.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page_title = "Data Kunjungan Tamu";
$message = $_SESSION['message'] ?? ''; // Ambil pesan dari session
$message_type = $_SESSION['message_type'] ?? ''; // Ambil tipe pesan
unset($_SESSION['message'], $_SESSION['message_type']); // Hapus pesan setelah ditampilkan

$csrf_delete_tamu_token = csrf_generate_token('delete_tamu');
$csrf_checkout_tamu_token = csrf_generate_token('checkout_tamu');

// Handle Aksi Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout' && isset($_POST['id'])) {
    if (!csrf_validate_token($_POST['csrf_token'] ?? '', 'checkout_tamu')) {
        $_SESSION['message'] = "Permintaan tidak valid. Silakan coba lagi.";
        $_SESSION['message_type'] = "danger";
        header("Location: data_tamu.php");
        exit;
    }

    $id_tamu_checkout = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if ($id_tamu_checkout) {
        // Set waktu saat ini
        $waktu_sekarang = date("H:i:s");
        $status_keluar = 'Keluar';

        $sql_checkout = "UPDATE tb_tamu SET waktu_keluar = ?, status_keluar = ? WHERE id_tamu = ?";
        if ($stmt_checkout = $koneksi->prepare($sql_checkout)) {
            $stmt_checkout->bind_param("ssi", $waktu_sekarang, $status_keluar, $id_tamu_checkout);
            if ($stmt_checkout->execute()) {
                $_SESSION['message'] = "Tamu berhasil di-checkout pada jam $waktu_sekarang.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal checkout tamu: " . $stmt_checkout->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt_checkout->close();
        } else {
            $_SESSION['message'] = "Gagal menyiapkan statement checkout: " . $koneksi->error;
            $_SESSION['message_type'] = "danger";
        }
    }
    header("Location: data_tamu.php");
    exit;
}

// Handle Aksi Hapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    if (!csrf_validate_token($_POST['csrf_token'] ?? '', 'delete_tamu')) {
        $_SESSION['message'] = "Permintaan tidak valid. Silakan coba lagi.";
        $_SESSION['message_type'] = "danger";
        header("Location: data_tamu.php");
        exit;
    }

    $id_tamu_to_delete = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if ($id_tamu_to_delete) {
        $sql_delete = "DELETE FROM tb_tamu WHERE id_tamu = ?";
        if ($stmt_delete = $koneksi->prepare($sql_delete)) {
            $stmt_delete->bind_param("i", $id_tamu_to_delete);
            if ($stmt_delete->execute()) {
                $_SESSION['message'] = "Data tamu berhasil dihapus.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Gagal menghapus data tamu: " . $stmt_delete->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt_delete->close();
        } else {
            $_SESSION['message'] = "Gagal menyiapkan statement hapus: " . $koneksi->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: data_tamu.php"); // Redirect untuk refresh dan menghilangkan parameter GET
        exit;
    } else {
        $_SESSION['message'] = "ID tamu tidak valid untuk dihapus.";
        $_SESSION['message_type'] = "danger";
        header("Location: data_tamu.php");
        exit;
    }
}


// Fetch semua data tamu, diurutkan berdasarkan tanggal dan waktu terbaru dulu
$tamu_list = [];
$sql_select_tamu = "SELECT id_tamu, tanggal_kunjungan, waktu_masuk, nama_tamu, asal_instansi, bertemu_dengan, keperluan, waktu_keluar FROM tb_tamu ORDER BY tanggal_kunjungan DESC, waktu_masuk DESC";
$result_tamu = $koneksi->query($sql_select_tamu);
if ($result_tamu && $result_tamu->num_rows > 0) {
    while ($row = $result_tamu->fetch_assoc()) {
        $tamu_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<?php include '_partials/head.php'; ?>
<style>
    .table-responsive { margin-top: 1rem; }
    .action-buttons .btn { margin-right: 5px; }
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
        <a href="export_tamu.php" class="btn btn-outline-success me-2">
            <i class="bi bi-file-earmark-excel-fill"></i> Ekspor ke Excel (XLS)
        </a>
        
        </div>
</div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'info'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary-gradient text-white">
                    <h5 class="mb-0">Daftar Kunjungan Tamu</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($tamu_list)): ?>
                    <div class="table-responsive">
                        <table id="tabelDataTamu" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal</th>
                                    <th>Waktu Masuk</th>
                                    <th>Nama Tamu</th>
                                    <th>Asal Instansi</th>
                                    <th>Bertemu Dengan</th>
                                    <th>Keperluan</th>
                                    <th>Waktu Keluar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $nomor = 1; foreach ($tamu_list as $tamu): ?>
                                <tr>
                                    <td><?php echo $nomor++; ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($tamu['tanggal_kunjungan']))); ?></td>
                                    <td><?php echo htmlspecialchars($tamu['waktu_masuk']); ?></td>
                                    <td><?php echo htmlspecialchars($tamu['nama_tamu']); ?></td>
                                    <td><?php echo htmlspecialchars($tamu['asal_instansi']); ?></td>
                                    <td><?php echo htmlspecialchars($tamu['bertemu_dengan']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($tamu['keperluan'])); ?></td>
                                    <td><?php echo htmlspecialchars($tamu['waktu_keluar'] ?? '-'); ?></td>
                                    <td class="action-buttons">
                                        <?php if (empty($tamu['waktu_keluar'])): ?>
                                        <form method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin checkout tamu ini?');">
                                            <input type="hidden" name="action" value="checkout">
                                            <input type="hidden" name="id" value="<?php echo (int) $tamu['id_tamu']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_checkout_tamu_token, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Checkout (Set Waktu Keluar Saat Ini)">
                                                <i class="bi bi-box-arrow-right"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="Sudah Checkout"><i class="bi bi-check-circle-fill"></i></button>
                                        <?php endif; ?>
                                    <a href="detail_tamu.php?id=<?php echo $tamu['id_tamu']; ?>" class="btn btn-sm btn-info" title="Lihat Detail Tamu">
    <i class="bi bi-eye-fill"></i>
</a>
<a href="edit_tamu.php?id=<?php echo $tamu['id_tamu']; ?>" class="btn btn-sm btn-warning" title="Edit Data Tamu">
    <i class="bi bi-pencil-fill"></i>
                                </a>
                                        <form method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data tamu ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $tamu['id_tamu']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_delete_tamu_token, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Data">
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
                        Belum ada data kunjungan tamu yang tersimpan.
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
            $('#tabelDataTamu').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" // Untuk bahasa Indonesia
                },
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                "pageLength": 10 // Jumlah entri default per halaman
            });
        });

        // Script untuk toggle sidebar di mobile (jika menggunakan tombol khusus)
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
