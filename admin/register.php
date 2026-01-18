<?php
/*
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php';

// Jika admin sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // $nama_lengkap = trim($_POST['nama_lengkap']); // Removed
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // Auto-fill nama_lengkap with username since field is removed but DB requires it
    $nama_lengkap = $username; 

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'admin'; // Default role for self-registration

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Semua kolom bertanda * wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        // Cek Username
        $sql_check = "SELECT id_admin FROM tb_admin WHERE username = ?";
        if ($stmt = $koneksi->prepare($sql_check)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "Username sudah digunakan.";
            }
            $stmt->close();
        }

        // Cek Email (jika diisi)
        if (empty($error) && !empty($email)) {
             $sql_check_email = "SELECT id_admin FROM tb_admin WHERE email = ?";
             if ($stmt_e = $koneksi->prepare($sql_check_email)) {
                 $stmt_e->bind_param("s", $email);
                 $stmt_e->execute();
                 $stmt_e->store_result();
                 if ($stmt_e->num_rows > 0) {
                     $error = "Email sudah terdaftar.";
                 }
                 $stmt_e->close();
             }
        }

        if (empty($error)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO tb_admin (nama_lengkap, username, password_hash, email, role) VALUES (?, ?, ?, ?, ?)";
            
            if ($stmt = $koneksi->prepare($sql_insert)) {
                $stmt->bind_param("sssss", $nama_lengkap, $username, $password_hash, $email, $role);
                if ($stmt->execute()) {
                    $success = "Registrasi berhasil! Silakan login.";
                    // Redirect delayed or show message
                    header("refresh:2;url=login.php"); 
                } else {
                    $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<?php 
$page_title = "Registrasi Admin Baru";
require_once '_partials/head.php'; 
?>
<body class="login-body d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: var(--bg-light);">
    <div class="card shadow-lg border-0 my-5" style="width: 100%; max-width: 500px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                 <h4 class="fw-bold text-dark">Daftar Akun Admin</h4>
                 <p class="text-muted small">Buat akun baru untuk mengakses dashboard</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success py-2 small"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Username <span class="text-danger">*</span></label>
                    <input type="text" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Konfirmasi <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary-custom fw-bold py-2">
                        <i class="bi bi-person-plus-fill"></i> Daftar Sekarang
                    </button>
                    <a href="login.php" class="btn btn-light text-muted py-2">Sudah punya akun? Login</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
