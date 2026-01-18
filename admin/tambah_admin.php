<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Hanya superadmin yang boleh menambah admin (opsional)
// if ($_SESSION['admin_role'] !== 'superadmin') {
//     $_SESSION['message'] = "Anda tidak memiliki hak akses untuk menambah admin.";
//     $_SESSION['message_type'] = "danger";
//     header("Location: manajemen_admin.php");
//     exit;
// }

$page_title = "Tambah Pengguna Admin Baru";
$errors = [];
$nama_lengkap = '';
$username = '';
$email = '';
$role = 'admin'; // Default role

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $role = $_POST['role'];

    // Validasi
    if (empty($nama_lengkap)) $errors[] = "Nama lengkap wajib diisi.";
    if (empty($username)) $errors[] = "Username wajib diisi.";
    if (empty($password)) $errors[] = "Password wajib diisi.";
    if ($password !== $konfirmasi_password) $errors[] = "Password dan konfirmasi password tidak cocok.";
    if (strlen($password) < 6 && !empty($password)) $errors[] = "Password minimal 6 karakter."; // Contoh validasi panjang password
    if (!in_array($role, ['admin', 'superadmin'])) $errors[] = "Role tidak valid.";

    // Cek apakah username sudah ada
    if (empty($errors) && !empty($username)) {
        $sql_check_username = "SELECT id_admin FROM tb_admin WHERE username = ?";
        if ($stmt_check = $koneksi->prepare($sql_check_username)) {
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Username sudah digunakan. Silakan pilih username lain.";
            }
            $stmt_check->close();
        }
    }
    
    // Cek apakah email sudah ada (jika email diisi dan unik)
    // Untuk saat ini, kita asumsikan email boleh duplikat atau tidak diisi,
    // Anda bisa tambahkan validasi unik untuk email jika diperlukan.

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql_insert = "INSERT INTO tb_admin (nama_lengkap, username, password_hash, email, role) VALUES (?, ?, ?, ?, ?)";
        if ($stmt_insert = $koneksi->prepare($sql_insert)) {
            $stmt_insert->bind_param("sssss", $nama_lengkap, $username, $password_hash, $email, $role);
            if ($stmt_insert->execute()) {
                $_SESSION['message'] = "Admin baru berhasil ditambahkan.";
                $_SESSION['message_type'] = "success";
                header("Location: manajemen_admin.php");
                exit;
            } else {
                $errors[] = "Gagal menambahkan admin: " . $stmt_insert->error;
                error_log("Gagal insert admin: " . $stmt_insert->error);
            }
            $stmt_insert->close();
        } else {
            $errors[] = "Gagal menyiapkan statement insert: " . $koneksi->error;
            error_log("Gagal prepare insert admin: " . $koneksi->error);
        }
    }
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
                <a href="manajemen_admin.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left-circle"></i> Kembali ke Daftar Admin
                </a>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Oops! Ada kesalahan:</h4>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary-gradient text-white">
                    <h5 class="mb-0">Formulir Tambah Admin</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($nama_lengkap); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email (Opsional)</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Minimal 6 karakter.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="konfirmasi_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="superadmin" <?php echo ($role === 'superadmin') ? 'selected' : ''; ?>>Super Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Tambah Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script untuk toggle sidebar
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