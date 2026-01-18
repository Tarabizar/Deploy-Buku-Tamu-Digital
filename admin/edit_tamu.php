<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php'; // Sesuaikan path jika berbeda

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page_title = "Update Waktu Keluar Tamu";
$tamu_info = null; // Untuk menampilkan info tamu
$id_tamu_to_edit = null;
$errors = [];
$success_message = '';

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id_tamu_to_edit = $_GET['id'];
    // Fetch data tamu yang akan diupdate waktu keluarnya
    // Kita hanya perlu beberapa info untuk ditampilkan dan waktu keluar saat ini
    $sql_get_tamu = "SELECT id_tamu, nama_tamu, tanggal_kunjungan, waktu_masuk, keperluan, status_keluar, waktu_keluar 
                     FROM tb_tamu 
                     WHERE id_tamu = ?";
    if ($stmt_get = $koneksi->prepare($sql_get_tamu)) {
        $stmt_get->bind_param("i", $id_tamu_to_edit);
        $stmt_get->execute();
        $result_tamu = $stmt_get->get_result();
        if ($result_tamu->num_rows === 1) {
            $tamu_info = $result_tamu->fetch_assoc();
        } else {
            $_SESSION['message'] = "Data tamu tidak ditemukan.";
            $_SESSION['message_type'] = "danger";
            header("Location: data_tamu.php");
            exit;
        }
        $stmt_get->close();
    } else {
        $_SESSION['message'] = "Gagal menyiapkan data tamu.";
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

// Inisialisasi waktu keluar dengan data yang ada atau kosongkan
$waktu_keluar_form = $tamu_info['waktu_keluar'] ?: '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $waktu_keluar_input = trim($_POST['waktu_keluar']);
    $status_keluar_baru = 'Keluar'; // Selalu set 'Keluar' saat form ini disubmit

    // Jika waktu keluar tidak diisi oleh admin, gunakan waktu saat ini
    if (empty($waktu_keluar_input)) {
        $waktu_keluar_final = date("H:i:s");
    } else {
        // Validasi format waktu jika diisi manual (opsional, input type="time" sudah membantu)
        if (!preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/", $waktu_keluar_input)) {
             $errors[] = "Format waktu keluar tidak valid. Gunakan HH:MM atau HH:MM:SS.";
        } else {
            $waktu_keluar_final = $waktu_keluar_input;
        }
    }

    if (empty($errors)) {
        $sql_update = "UPDATE tb_tamu SET status_keluar = ?, waktu_keluar = ? WHERE id_tamu = ?";
        
        if ($stmt_update = $koneksi->prepare($sql_update)) {
            $stmt_update->bind_param("ssi", 
                $status_keluar_baru, 
                $waktu_keluar_final, 
                $id_tamu_to_edit
            );

            if ($stmt_update->execute()) {
                $_SESSION['message'] = "Waktu keluar tamu '" . htmlspecialchars($tamu_info['nama_tamu']) . "' berhasil diperbarui.";
                $_SESSION['message_type'] = "success";
                header("Location: data_tamu.php"); // Kembali ke daftar tamu
                exit;
            } else {
                $errors[] = "Gagal memperbarui waktu keluar tamu: " . $stmt_update->error;
                error_log("Gagal update waktu keluar tb_tamu: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            $errors[] = "Gagal menyiapkan statement update waktu keluar: " . $koneksi->error;
            error_log("Gagal prepare update waktu keluar tb_tamu: " . $koneksi->error);
        }
    }
    // Jika ada error, $waktu_keluar_form akan berisi data POST terbaru untuk diisi kembali ke form
    $waktu_keluar_form = $waktu_keluar_input; // Tampilkan kembali input user jika ada error
}
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

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading"><i class="bi bi-exclamation-octagon-fill"></i> Oops! Ada kesalahan:</h4>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($success_message): // Untuk pesan sukses dari halaman ini sendiri, jika tidak redirect ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>


            <?php if ($tamu_info): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-gold-gradient text-white">
                    <h5 class="mb-0">Update Waktu Keluar untuk: <?php echo htmlspecialchars($tamu_info['nama_tamu']); ?> (ID: <?php echo $tamu_info['id_tamu']; ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="info-tamu-group">
                        <span class="info-tamu-label">Nama Tamu:</span>
                        <span><?php echo htmlspecialchars($tamu_info['nama_tamu']); ?></span>
                    </div>
                    <div class="info-tamu-group">
                        <span class="info-tamu-label">Tanggal Kunjungan:</span>
                        <span><?php echo htmlspecialchars(date('d F Y', strtotime($tamu_info['tanggal_kunjungan']))); ?></span>
                    </div>
                     <div class="info-tamu-group">
                        <span class="info-tamu-label">Waktu Masuk:</span>
                        <span><?php echo htmlspecialchars($tamu_info['waktu_masuk']); ?></span>
                    </div>
                    <div class="info-tamu-group">
                        <span class="info-tamu-label">Keperluan:</span>
                        <span><?php echo nl2br(htmlspecialchars($tamu_info['keperluan'])); ?></span>
                    </div>
                     <div class="info-tamu-group">
                        <span class="info-tamu-label">Status Saat Ini:</span>
                        <span class="badge bg-<?php echo $tamu_info['status_keluar'] == 'Keluar' ? 'secondary' : 'success'; ?>">
                            <?php echo htmlspecialchars($tamu_info['status_keluar']); ?>
                        </span>
                        <?php if($tamu_info['waktu_keluar']): ?>
                            (pada <?php echo htmlspecialchars($tamu_info['waktu_keluar']); ?>)
                        <?php endif; ?>
                    </div>
                    <hr>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $id_tamu_to_edit; ?>">
                        <div class="mb-3">
                            <label for="waktu_keluar" class="form-label">
                                <?php echo ($tamu_info['status_keluar'] === 'Keluar' && $tamu_info['waktu_keluar']) ? 'Ubah Waktu Keluar:' : 'Set Waktu Keluar:'; ?>
                            </label>
                            <input type="time" class="form-control" id="waktu_keluar" name="waktu_keluar" value="<?php echo htmlspecialchars($waktu_keluar_form); ?>">
                            <div class="form-text">
                                <?php echo ($tamu_info['status_keluar'] === 'Keluar' && $tamu_info['waktu_keluar']) ? 'Isi untuk mengubah waktu keluar yang sudah tercatat.' : 'Kosongkan untuk menggunakan waktu saat ini, atau isi manual.'; ?>
                                <br>Menyimpan form ini akan otomatis mengubah status menjadi "Keluar".
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-clock-history"></i> 
                                <?php echo ($tamu_info['status_keluar'] === 'Keluar' && $tamu_info['waktu_keluar']) ? 'Simpan Perubahan Waktu Keluar' : 'Tandai Sudah Keluar & Simpan Waktu'; ?>
                            </button>
                            <a href="detail_tamu.php?id=<?php echo $id_tamu_to_edit; ?>" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Batal</a>
                        </div>
                    </form>
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