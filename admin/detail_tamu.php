<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php'; // Sesuaikan path jika berbeda
require_once __DIR__ . '/../koneksi/csrf.php';

// Generate CSRF token for checkout
$csrf_checkout_token = csrf_generate_token('checkout_tamu_detail');

// Handle POST request (Checkout)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout' && isset($_POST['id'])) {
    if (!csrf_validate_token($_POST['csrf_token'] ?? '', 'checkout_tamu_detail')) {
        $_SESSION['message'] = "Permintaan tidak valid. Silakan coba lagi.";
        $_SESSION['message_type'] = "danger";
        // Redirect back to same page to show error
        header("Location: detail_tamu.php?id=" . (int)$_POST['id']);
        exit;
    }

    $id_tamu_checkout = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    if ($id_tamu_checkout) {
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
            $_SESSION['message'] = "Gagal menyiapkan statement: " . $koneksi->error;
            $_SESSION['message_type'] = "danger";
        }
    }
    // Refresh page to show updated status
    header("Location: detail_tamu.php?id=" . $id_tamu_checkout);
    exit;
}

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page_title = "Detail Kunjungan Tamu";
$tamu_detail = null;
$error_message = '';

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id_tamu_to_view = $_GET['id'];

    // Fetch detail data tamu
    // Memilih semua kolom yang relevan dari tb_tamu
    $sql_select_detail = "SELECT id_tamu, tanggal_kunjungan, waktu_masuk, nama_tamu, 
                                 asal_instansi, jabatan, no_telepon, email_tamu, 
                                 bertemu_dengan, keperluan, catatan_tambahan, 
                                 foto_tamu, tanda_tangan, status_keluar, waktu_keluar, created_at 
                          FROM tb_tamu 
                          WHERE id_tamu = ?";
    
    if ($stmt_detail = $koneksi->prepare($sql_select_detail)) {
        $stmt_detail->bind_param("i", $id_tamu_to_view);
        $stmt_detail->execute();
        $result_detail = $stmt_detail->get_result();
        if ($result_detail->num_rows === 1) {
            $tamu_detail = $result_detail->fetch_assoc();
        } else {
            $error_message = "Data tamu tidak ditemukan.";
            // Simpan pesan error di session untuk ditampilkan setelah redirect
            $_SESSION['message'] = $error_message;
            $_SESSION['message_type'] = "danger";
            header("Location: data_tamu.php");
            exit;
        }
        $stmt_detail->close();
    } else {
        $error_message = "Gagal menyiapkan statement untuk mengambil detail tamu: " . $koneksi->error;
        // Tampilkan error ini atau redirect dengan pesan
        error_log("SQL Prepare error for detail_tamu: " . $koneksi->error);
        $_SESSION['message'] = "Terjadi kesalahan saat mengambil data.";
        $_SESSION['message_type'] = "danger";
        header("Location: data_tamu.php");
        exit;
    }
} else {
    $_SESSION['message'] = "ID tamu tidak valid atau tidak disediakan.";
    $_SESSION['message_type'] = "warning";
    header("Location: data_tamu.php");
    exit;
}

// $koneksi->close(); // Ditutup otomatis
?>
<!DOCTYPE html>
<html lang="id">
<?php include '_partials/head.php'; ?>
<body>
    <?php
    if (file_exists(__DIR__ . '/_partials/navbar.php')) { include_once __DIR__ . '/_partials/navbar.php'; }
    if (file_exists(__DIR__ . '/_partials/sidebar.php')) { include_once __DIR__ . '/_partials/sidebar.php'; }
    ?>

    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
                <a href="data_tamu.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Tamu
                </a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                <?php 
                    echo htmlspecialchars($_SESSION['message']); 
                    unset($_SESSION['message'], $_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if ($error_message && !$tamu_detail): // Tampilkan error jika tamu_detail tidak berhasil di-fetch ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <?php if ($tamu_detail): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-primary-gradient text-white">
                    <h5 class="mb-0">Detail Tamu: <?php echo htmlspecialchars($tamu_detail['nama_tamu']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-group">
                                <span class="detail-label">ID Tamu:</span>
                                <span class="detail-value"><?php echo $tamu_detail['id_tamu']; ?></span>
                            </div>
                            <div class="detail-group">
                                <span class="detail-label">Tanggal Kunjungan:</span>
                                <span class="detail-value"><?php echo htmlspecialchars(date('d F Y', strtotime($tamu_detail['tanggal_kunjungan']))); ?></span>
                            </div>
                            <div class="detail-group">
                                <span class="detail-label">Waktu Masuk:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tamu_detail['waktu_masuk']); ?></span>
                            </div>
                            <div class="detail-group">
                                <span class="detail-label">Nama Tamu:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tamu_detail['nama_tamu']); ?></span>
                            </div>
                            <div class="detail-group">
                                <span class="detail-label">Asal Tamu:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tamu_detail['asal_instansi'] ?: '-'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-group">
                                <span class="detail-label">No. Telepon:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tamu_detail['no_telepon'] ?: '-'); ?></span>
                            </div>
                            <div class="detail-group">
                                <span class="detail-label">Bertemu Dengan:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tamu_detail['bertemu_dengan']); ?></span>
                            </div>
                            <div class="detail-group">
                                <span class="detail-label">Waktu Keluar:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tamu_detail['waktu_keluar'] ?: 'Belum keluar'); ?></span>
                            </div>
                             <div class="detail-group">
                                <span class="detail-label">Status Kunjungan:</span>
                                <span class="badge bg-<?php echo $tamu_detail['status_keluar'] == 'Keluar' ? 'secondary' : 'success'; ?>">
                                    <?php echo htmlspecialchars($tamu_detail['status_keluar']); ?>
                                </span>
                            </div>
                            <div class="detail-group">
                                <span class="detail-label">Dicatat Pada:</span>
                                <span class="detail-value"><?php echo htmlspecialchars(date('d M Y, H:i:s', strtotime($tamu_detail['created_at']))); ?></span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="detail-group">
                        <span class="detail-label d-block">Keperluan Kunjungan:</span>
                        <div class="detail-value p-2 bg-light border rounded" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($tamu_detail['keperluan'])); ?></div>
                    </div>
                    <div class="detail-group mt-2">
                        <span class="detail-label d-block">Catatan Tambahan:</span>
                        <div class="detail-value p-2 bg-light border rounded" style="white-space: pre-wrap;"><?php echo $tamu_detail['catatan_tambahan'] ? nl2br(htmlspecialchars($tamu_detail['catatan_tambahan'])) : '<em>Tidak ada catatan tambahan.</em>'; ?></div>
                    </div>

                    <?php if ($tamu_detail['foto_tamu'] || $tamu_detail['tanda_tangan']): ?>
                    <hr>
                    <div class="row mt-3">
                        <?php if ($tamu_detail['foto_tamu']): ?>
                        <div class="col-md-6">
                            <span class="detail-label d-block">Foto Tamu:</span>
                            <?php 
                                $path_foto_tamu = '../uploads/tamu/' . htmlspecialchars($tamu_detail['foto_tamu']);
                                if (file_exists($path_foto_tamu) && !empty($tamu_detail['foto_tamu'])) {
                                    echo '<img src="' . $path_foto_tamu . '" alt="Foto Tamu" class="foto-tamu-detail">';
                                } else {
                                    echo '<span class="detail-value"><em>Foto tidak tersedia.</em></span>';
                                }
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                </div>
                <div class="card-footer bg-white d-flex justify-content-end gap-2 p-3">
                    <?php if (empty($tamu_detail['waktu_keluar'])): ?>
                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin checkout tamu ini?');">
                        <input type="hidden" name="action" value="checkout">
                        <input type="hidden" name="id" value="<?php echo (int) $tamu_detail['id_tamu']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_checkout_token, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-success" title="Checkout (Set Waktu Keluar Saat Ini)">
                            <i class="bi bi-box-arrow-right"></i> Checkout Sekarang
                        </button>
                    </form>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="bi bi-check-circle-fill"></i> Sudah Checkout
                        </button>
                    <?php endif; ?>
                    
                    <a href="edit_tamu.php?id=<?php echo $tamu_detail['id_tamu']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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