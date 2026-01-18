<?php 
/*
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php';

$step = 1;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_user'])) {
        // Step 1: Verifikasi User
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        
        if (empty($username) || empty($email)) {
            $error = "Username dan Email wajib diisi.";
        } else {
            $sql = "SELECT id_admin FROM tb_admin WHERE username = ? AND email = ?";
            if ($stmt = $koneksi->prepare($sql)) {
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id_admin);
                    $stmt->fetch();
                    $_SESSION['reset_admin_id'] = $id_admin;
                    $step = 2;
                } else {
                    $error = "Data tidak ditemukan. Pastikan Username dan Email benar.";
                }
                $stmt->close();
            } else {
                $error = "Terjadi kesalahan sistem saat memverifikasi.";
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        // Step 2: Reset Password
        $step = 2; // Stay on step 2 if error
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $id_admin = $_SESSION['reset_admin_id'] ?? null;
        
        if (!$id_admin) {
            $error = "Sesi telah berakhir. Silakan ulangi proses verifikasi.";
            $step = 1;
        } elseif (empty($new_password) || empty($confirm_password)) {
            $error = "Password baru wajib diisi.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Konfirmasi password tidak cocok.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password minimal 6 karakter.";
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE tb_admin SET password_hash = ? WHERE id_admin = ?";
            if ($stmt = $koneksi->prepare($sql)) {
                $stmt->bind_param("si", $password_hash, $id_admin);
                if ($stmt->execute()) {
                    unset($_SESSION['reset_admin_id']);
                    // Optional: Destroy session if needed, but better just unset the sensitive var
                    // session_destroy(); 
                    header("Location: login.php?resetsuccess=1");
                    exit;
                } else {
                    $error = "Gagal mengupdate password database.";
                }
                $stmt->close();
            }
        }
    }
} else {
    // Jika akses GET dan ada flag step 2 di session? Tidak, default ke step 1.
    // Kecuali jika form resubmission.
    if (isset($_SESSION['reset_admin_id'])) {
        // Maybe allow staying on step 2 if session is active? 
        // But better to force re-verify if coming from fresh GET to be safe/simple?
        // Let's keep it simple: if POST fails, we stay in step 2 logic above.
        // If fresh GET, step 1.
         unset($_SESSION['reset_admin_id']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<?php 
$page_title = "Lupa Password Admin";
require_once '_partials/head.php'; 
?>
<body class="login-body d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: var(--bg-light);">
    <div class="card shadow-lg border-0" style="width: 100%; max-width: 450px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                 <div class="mb-3">
                    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
                 </div>
                 <h4 class="fw-bold text-dark">Pemulihan Password</h4>
                 <p class="text-muted small">Reset password admin menggunakan Username & Email</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <?php if ($step == 1): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-secondary"></i></span>
                        <input type="text" name="username" class="form-control border-start-0 ps-0" required placeholder="Masukkan Username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Email Terdaftar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-secondary"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0" required placeholder="Masukkan Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="verify_user" class="btn btn-primary-custom fw-bold py-2">
                        Verifikasi Akun <i class="bi bi-arrow-right"></i>
                    </button>
                    <a href="login.php" class="btn btn-light text-muted py-2">Kembali ke Login</a>
                </div>
            </form>
            <?php else: ?>
            <form method="POST" action="">
                <div class="alert alert-info py-2 small d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>Akun terverifikasi. Silakan buat password baru.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Password Baru</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-secondary"></i></span>
                        <input type="password" name="new_password" class="form-control border-start-0 ps-0" required placeholder="Minimal 6 karakter" minlength="6">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-check2-all text-secondary"></i></span>
                        <input type="password" name="confirm_password" class="form-control border-start-0 ps-0" required placeholder="Ulangi Password Baru">
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="reset_password" class="btn btn-success fw-bold py-2 text-white">
                        <i class="bi bi-save"></i> Simpan Password Baru
                    </button>
                    <a href="login.php" class="btn btn-light text-muted py-2">Batal</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
