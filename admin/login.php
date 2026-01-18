<?php
session_start(); // Mulai session di awal

// Jika admin sudah login, redirect ke dashboard admin
// Jika admin sudah login, redirect ke dashboard admin
// if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
//     header("Location: index.php");
//     exit;
// }

// Memanggil file koneksi dari parent directory
// Pastikan path ini benar sesuai struktur folder Anda
require_once __DIR__ . '/../koneksi/koneksi.php';

// Fetch company profile for logo
$logo_path = '';
$sql_profile = "SELECT foto FROM tb_profile LIMIT 1";
$result_profile = $koneksi->query($sql_profile);
if ($result_profile && $result_profile->num_rows > 0) {
    $row_profile = $result_profile->fetch_assoc();
    if (!empty($row_profile['foto']) && file_exists(__DIR__ . '/images/' . $row_profile['foto'])) {
        $logo_path = 'images/' . $row_profile['foto'];
    }
}


$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"])) || empty(trim($_POST["password"]))) {
        $login_error = "Username dan Password wajib diisi.";
    } else {
        $username = trim($_POST["username"]);
        $password = trim($_POST["password"]); // Password plain text dari form

        $sql = "SELECT id_admin, username, password_hash, nama_lengkap, role FROM tb_admin WHERE username = ?";

        if ($stmt = $koneksi->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) { // Username ditemukan
                    $stmt->bind_result($id_admin, $db_username, $db_password_hash, $nama_lengkap, $role);
                    if ($stmt->fetch()) {
                        // Verifikasi password plain text dengan hash dari database
                        if (password_verify($password, $db_password_hash)) {
                            // Password benar, mulai session baru
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $id_admin;
                            $_SESSION['admin_username'] = $db_username;
                            $_SESSION['admin_nama_lengkap'] = $nama_lengkap;
                            $_SESSION['admin_role'] = $role;

                            // Update last_login (opsional)
                            $update_sql = "UPDATE tb_admin SET last_login = CURRENT_TIMESTAMP WHERE id_admin = ?";
                            if ($update_stmt = $koneksi->prepare($update_sql)) {
                                $update_stmt->bind_param("i", $id_admin);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }

                            header("Location: index.php"); // Redirect ke dashboard admin
                            exit;
                        } else {
                            $login_error = "Password yang Anda masukkan salah.";
                        }
                    }
                } else {
                    $login_error = "Username tidak ditemukan.";
                }
            } else {
                $login_error = "Oops! Terjadi kesalahan saat eksekusi. Silakan coba lagi nanti.";
                error_log("Admin login execute error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $login_error = "Oops! Gagal menyiapkan statement. Silakan coba lagi nanti.";
            error_log("Admin login prepare error: " . $koneksi->error);
        }
    }
    // $koneksi->close(); // Sebaiknya tidak ditutup di sini jika halaman masih membutuhkan koneksi setelahnya,
                       // tapi karena ini hanya untuk login, bisa saja ditutup. PHP akan menutup otomatis di akhir skrip.
}
?>
<!DOCTYPE html>
<html lang="id">
<?php 
$page_title = "Login Admin";
require_once '_partials/head.php'; 
?>
<body class="login-body">
    <div class="login-card">
        <div class="row g-0">
            <!-- Left Side - Image/Branding -->
            <div class="col-md-6 d-none d-md-flex flex-column justify-content-center align-items-center text-white p-5" 
                 style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);">
                <div class="text-center mb-4">
                     <!-- Logo -->
                     <?php if (!empty($logo_path)): ?>
                        <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo Kementerian" class="img-fluid drop-shadow" style="max-height: 280px; width: auto;">
                     <?php else: ?>
                        <i class="bi bi-kaaba" style="font-size: 8rem; color: var(--secondary-color); text-shadow: 0 4px 8px rgba(0,0,0,0.2);"></i>
                     <?php endif; ?>
                </div>

                <h2 class="fw-bold mb-3 text-center display-6" style="text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Kementerian Haji & Umrah</h2>
                <h4 class="fw-normal text-center text-white-50 letter-spacing-1">Buku Tamu Digital</h4>
            </div>

            <!-- Right Side - Login Form -->
            <div class="col-md-6 bg-white p-5">
                <div class="d-flex align-items-center mb-4 pb-2 border-bottom">
                    <i class="bi bi-shield-lock-fill fs-2 me-2" style="color: var(--primary-color);"></i>
                    <h4 class="mb-0 fw-bold" style="color: var(--primary-color);">Login Admin</h4>
                </div>

                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?php echo htmlspecialchars($login_error); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['resetsuccess'])): ?>
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <div>Password berhasil diubah. Silakan login.</div>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label text-muted small fw-bold text-uppercase">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-secondary"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Masukkan ID Pengguna" required
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label text-muted small fw-bold text-uppercase">Password</label>
                            <!--<a href="lupa_password.php" class="text-decoration-none small" style="color: var(--primary-color);">Lupa Password?</a>-->
                        </div>

                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-secondary"></i></span>
                            <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Masukkan Kata Sandi" required>
                        </div>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary-custom py-2 fw-bold shadow-sm">
                            MASUK <i class="bi bi-arrow-right-short"></i>
                        </button>
                    </div>
                    <!--
                    <div class="text-center mb-3">
                        <span class="text-muted small">Belum punya akun?</span>
                        <a href="register.php" class="text-decoration-none fw-bold" style="color: var(--primary-color);">Daftar di sini</a>
                    </div> -->

                </form>

                <div class="text-center mt-3">
                    <a href="../index.php" class="text-decoration-none text-muted small">
                        <i class="bi bi-arrow-left"></i> Kembali ke Halaman Tamu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>