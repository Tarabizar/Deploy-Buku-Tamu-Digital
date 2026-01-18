<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php'; // Pastikan path koneksi benar
require_once __DIR__ . '/../koneksi/csrf.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Opsional: Batasi akses hanya untuk superadmin
// if ($_SESSION['admin_role'] !== 'superadmin') {
//     $_SESSION['message'] = "Anda tidak memiliki hak akses untuk halaman ini.";
//     $_SESSION['message_type'] = "danger";
//     header("Location: index.php"); // Redirect ke dashboard
//     exit;
// }

$page_title = "Manajemen Pengguna Admin";
// Ambil pesan dari session jika ada, lalu hapus agar tidak muncul lagi
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

// Siapkan token CSRF untuk aksi hapus
$csrf_delete_admin_token = csrf_generate_token('delete_admin');

// --- AWAL BLOK HANDLE AKSI HAPUS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    if (!csrf_validate_token($_POST['csrf_token'] ?? '', 'delete_admin')) {
        $_SESSION['message'] = "Permintaan tidak valid. Silakan coba lagi.";
        $_SESSION['message_type'] = "danger";
        header("Location: manajemen_admin.php");
        exit;
    }

    $id_admin_to_delete = filter_var($_POST['id'], FILTER_VALIDATE_INT);

    if ($id_admin_to_delete) {
        // Proteksi 1: Admin tidak bisa menghapus dirinya sendiri
        if ($id_admin_to_delete == $_SESSION['admin_id']) {
            $_SESSION['message'] = "Anda tidak dapat menghapus akun Anda sendiri.";
            $_SESSION['message_type'] = "danger";
        } else {
            // Proteksi 2: Superadmin terakhir tidak boleh dihapus (jika yang dihapus adalah superadmin)
            $can_delete = true; // Anggap bisa dihapus awalnya

            // Cek dulu role admin yang akan dihapus
            $role_admin_to_delete = '';
            $sql_get_role = "SELECT role FROM tb_admin WHERE id_admin = ?";
            if ($stmt_get_role = $koneksi->prepare($sql_get_role)) {
                $stmt_get_role->bind_param("i", $id_admin_to_delete);
                $stmt_get_role->execute();
                $result_role = $stmt_get_role->get_result();
                if ($result_role->num_rows === 1) {
                    $admin_details = $result_role->fetch_assoc();
                    $role_admin_to_delete = $admin_details['role'];
                }
                $stmt_get_role->close();
            }

            if ($role_admin_to_delete === 'superadmin') {
                // Hitung jumlah superadmin yang ada
                $sql_count_super = "SELECT COUNT(*) as total_super FROM tb_admin WHERE role = 'superadmin'";
                $result_count_super = $koneksi->query($sql_count_super);
                if ($result_count_super) {
                    $count_data = $result_count_super->fetch_assoc();
                    if ($count_data['total_super'] <= 1) {
                        // Jika hanya ada 1 atau kurang superadmin (dan yang mau dihapus adalah superadmin itu)
                        $_SESSION['message'] = "Tidak dapat menghapus. Harus ada minimal satu Super Admin yang tersisa.";
                        $_SESSION['message_type'] = "danger";
                        $can_delete = false;
                    }
                } else {
                     // Gagal query hitung superadmin, anggap tidak bisa dihapus untuk keamanan
                    $_SESSION['message'] = "Gagal memverifikasi jumlah Super Admin. Penghapusan dibatalkan.";
                    $_SESSION['message_type'] = "danger";
                    $can_delete = false;
                    error_log("Gagal query hitung superadmin: " . $koneksi->error);
                }
            }

            // Jika semua proteksi lolos, baru lakukan penghapusan
            if ($can_delete) {
                $sql_delete = "DELETE FROM tb_admin WHERE id_admin = ?";
                if ($stmt_delete = $koneksi->prepare($sql_delete)) {
                    $stmt_delete->bind_param("i", $id_admin_to_delete);
                    if ($stmt_delete->execute()) {
                        $_SESSION['message'] = "Pengguna admin berhasil dihapus.";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Gagal menghapus pengguna admin: " . $stmt_delete->error;
                        $_SESSION['message_type'] = "danger";
                        error_log("Admin Gagal Hapus Admin User: " . $stmt_delete->error);
                    }
                    $stmt_delete->close();
                } else {
                    $_SESSION['message'] = "Gagal menyiapkan statement hapus admin: " . $koneksi->error;
                    $_SESSION['message_type'] = "danger";
                    error_log("Admin Gagal Prepare Hapus Admin User: " . $koneksi->error);
                }
            }
        }
    } else {
        $_SESSION['message'] = "ID Admin tidak valid untuk dihapus.";
        $_SESSION['message_type'] = "danger";
    }
    // Redirect kembali ke halaman manajemen_admin.php untuk refresh dan menghilangkan parameter GET
    header("Location: manajemen_admin.php");
    exit;
}
// --- AKHIR BLOK HANDLE AKSI HAPUS ---


// Fetch semua data admin untuk ditampilkan di tabel
$admin_list = [];
$sql_select_admins = "SELECT id_admin, nama_lengkap, username, email, role, last_login, created_at FROM tb_admin ORDER BY id_admin ASC";
$result_admins = $koneksi->query($sql_select_admins);
if ($result_admins && $result_admins->num_rows > 0) {
    while ($row = $result_admins->fetch_assoc()) {
        $admin_list[] = $row;
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
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="tambah_admin.php" class="btn btn-primary-custom">
                        <i class="bi bi-plus-circle"></i> Tambah Admin Baru
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
                    <h5 class="mb-0">Daftar Pengguna Admin</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($admin_list)): ?>
                    <div class="table-responsive">
                        <table id="tabelDataAdmin" class="table table-striped table-hover table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Login Terakhir</th>
                                    <th>Dibuat Pada</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admin_list as $admin): ?>
                                <tr>
                                    <td><?php echo $admin['id_admin']; ?></td>
                                    <td><?php echo htmlspecialchars($admin['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['email'] ?? '-'); ?></td>
                                    <td><span class="badge bg-<?php echo $admin['role'] == 'superadmin' ? 'primary' : 'secondary'; ?>"><?php echo ucfirst(htmlspecialchars($admin['role'])); ?></span></td>
                                    <td><?php echo $admin['last_login'] ? htmlspecialchars(date('d M Y, H:i', strtotime($admin['last_login']))) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($admin['created_at']))); ?></td>
                                    <td class="action-buttons">
                                        <a href="edit_admin.php?id=<?php echo $admin['id_admin']; ?>" class="btn btn-sm btn-warning" title="Edit Admin">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <?php // Admin tidak bisa menghapus dirinya sendiri
                                        if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] != $admin['id_admin']): ?>
                                        <form method="POST" class="d-inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus admin ini? Tindakan ini tidak bisa diurungkan.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int) $admin['id_admin']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_delete_admin_token, ENT_QUOTES, 'UTF-8'); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus Admin">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="Aksi tidak diizinkan"><i class="bi bi-trash-fill"></i></button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">Belum ada pengguna admin. Silakan tambahkan admin baru.</div>
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
            $('#tabelDataAdmin').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
                "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
                "pageLength": 10,
                "order": [[ 0, "asc" ]], // Urutkan berdasarkan ID Admin
                "columnDefs": [
                    { "orderable": false, "targets": 7 } // Kolom "Aksi" tidak bisa diurutkan
                ]
            });
        });

        // Script untuk toggle sidebar
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn'); // Pastikan ID ini ada di tombol navbar
        const adminSidebar = document.getElementById('adminSidebar'); // Pastikan ID ini ada di div sidebar
        if (sidebarToggleBtn && adminSidebar) {
            sidebarToggleBtn.addEventListener('click', function() {
                adminSidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>
