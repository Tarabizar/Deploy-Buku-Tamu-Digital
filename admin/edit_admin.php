<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Opsional: Hanya superadmin yang boleh mengedit (termasuk dirinya sendiri)
// if ($_SESSION['admin_role'] !== 'superadmin') {
//     // Kecuali jika dia mengedit profilnya sendiri (bisa jadi halaman terpisah 'profil_saya.php')
//     if (!isset($_GET['id']) || $_GET['id'] != $_SESSION['admin_id']) {
//         $_SESSION['message'] = "Anda tidak memiliki hak akses untuk mengedit admin lain.";
//         $_SESSION['message_type'] = "danger";
//         header("Location: manajemen_admin.php");
//         exit;
//     }
// }

$page_title = "Edit Pengguna Admin";
$errors = [];
$admin_to_edit = null;
$id_admin_to_edit = null;

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id_admin_to_edit = $_GET['id'];
    $sql_get_admin = "SELECT id_admin, nama_lengkap, username, email, role FROM tb_admin WHERE id_admin = ?";
    if ($stmt_get = $koneksi->prepare($sql_get_admin)) {
        $stmt_get->bind_param("i", $id_admin_to_edit);
        $stmt_get->execute();
        $result_admin = $stmt_get->get_result();
        if ($result_admin->num_rows === 1) {
            $admin_to_edit = $result_admin->fetch_assoc();
        } else {
            $_SESSION['message'] = "Admin tidak ditemukan.";
            $_SESSION['message_type'] = "danger";
            header("Location: manajemen_admin.php");
            exit;
        }
        $stmt_get->close();
    } else {
        // Error prepare
        $_SESSION['message'] = "Gagal menyiapkan data admin untuk diedit.";
        $_SESSION['message_type'] = "danger";
        header("Location: manajemen_admin.php");
        exit;
    }
} else {
    $_SESSION['message'] = "ID Admin tidak valid atau tidak disediakan.";
    $_SESSION['message_type'] = "danger";
    header("Location: manajemen_admin.php");
    exit;
}


// Inisialisasi variabel form dengan data admin yang ada
$nama_lengkap = $admin_to_edit['nama_lengkap'];
$username = $admin_to_edit['username'];
$email = $admin_to_edit['email'];
$role = $admin_to_edit['role'];


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $new_username = trim($_POST['username']); // Username baru, bisa sama atau beda
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $konfirmasi_new_password = $_POST['konfirmasi_new_password'];
    $new_role = $_POST['role'];

    // Validasi
    if (empty($nama_lengkap)) $errors[] = "Nama lengkap wajib diisi.";
    if (empty($new_username)) $errors[] = "Username wajib diisi.";
    if (!empty($new_password) && $new_password !== $konfirmasi_new_password) $errors[] = "Password baru dan konfirmasi password tidak cocok.";
    if (!empty($new_password) && strlen($new_password) < 6) $errors[] = "Password baru minimal 6 karakter.";
    if (!in_array($new_role, ['admin', 'superadmin'])) $errors[] = "Role tidak valid.";

    // Cek apakah username baru (jika diubah) sudah ada & bukan username lama dari user ini
    if (empty($errors) && $new_username !== $admin_to_edit['username']) {
        $sql_check_username = "SELECT id_admin FROM tb_admin WHERE username = ?";
        if ($stmt_check = $koneksi->prepare($sql_check_username)) {
            $stmt_check->bind_param("s", $new_username);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors[] = "Username '$new_username' sudah digunakan. Silakan pilih username lain.";
            }
            $stmt_check->close();
        }
    }

    // Tidak boleh mengubah role diri sendiri dari superadmin ke admin jika dia satu-satunya superadmin
    if ($id_admin_to_edit == $_SESSION['admin_id'] && $admin_to_edit['role'] == 'superadmin' && $new_role == 'admin') {
        $sql_count_superadmin = "SELECT COUNT(*) as total_superadmin FROM tb_admin WHERE role = 'superadmin'";
        $res_count = $koneksi->query($sql_count_superadmin);
        $row_count = $res_count->fetch_assoc();
        if ($row_count['total_superadmin'] <= 1) {
            $errors[] = "Tidak dapat mengubah role. Harus ada minimal satu Super Admin.";
        }
    }


    if (empty($errors)) {
        $params = [];
        $types = "";
        $sql_update = "UPDATE tb_admin SET nama_lengkap = ?, username = ?, email = ?, role = ?";
        $params[] = $nama_lengkap; $types .= "s";
        $params[] = $new_username; $types .= "s";
        $params[] = $email;        $types .= "s";
        $params[] = $new_role;     $types .= "s";

        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update .= ", password_hash = ?";
            $params[] = $password_hash; $types .= "s";
        }

        $sql_update .= " WHERE id_admin = ?";
        $params[] = $id_admin_to_edit; $types .= "i";

        if ($stmt_update = $koneksi->prepare($sql_update)) {
            $stmt_update->bind_param($types, ...$params); // Spread operator untuk bind parameter
            if ($stmt_update->execute()) {
                $_SESSION['message'] = "Data admin berhasil diperbarui.";
                $_SESSION['message_type'] = "success";
                // Jika admin mengedit dirinya sendiri, update data session
                if ($id_admin_to_edit == $_SESSION['admin_id']) {
                    $_SESSION['admin_username'] = $new_username;
                    $_SESSION['admin_nama_lengkap'] = $nama_lengkap;
                    $_SESSION['admin_role'] = $new_role;
                }
                header("Location: manajemen_admin.php");
                exit;
            } else {
                $errors[] = "Gagal memperbarui admin: " . $stmt_update->error;
                error_log("Gagal update admin: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            $errors[] = "Gagal menyiapkan statement update admin: " . $koneksi->error;
            error_log("Gagal prepare update admin: " . $koneksi->error);
        }
    }
    // Jika ada error, variabel $username, $email, $role akan berisi data POST terbaru untuk diisi kembali ke form
    $username = $new_username; // Agar form menampilkan username yang baru diinput jika ada error
    $role = $new_role;
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

            <?php if ($admin_to_edit): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-primary-gradient text-white">
                    <h5 class="mb-0">Formulir Edit Admin: <?php echo htmlspecialchars($admin_to_edit['username']); ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $id_admin_to_edit; ?>">
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
                        <hr>
                        <p class="text-muted">Kosongkan field password jika tidak ingin mengubah password.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <div class="form-text">Minimal 6 karakter jika diisi.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="konfirmasi_new_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="konfirmasi_new_password" name="konfirmasi_new_password">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <?php // Hanya tampilkan opsi Super Admin jika yang login adalah Super Admin
                                // Atau jika admin yang diedit adalah Super Admin (agar tidak 'downgrade' tanpa sengaja)
                                if ($_SESSION['admin_role'] == 'superadmin' || $admin_to_edit['role'] == 'superadmin'): ?>
                                <option value="superadmin" <?php echo ($role === 'superadmin') ? 'selected' : ''; ?>>Super Admin</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Simpan Perubahan</button>
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