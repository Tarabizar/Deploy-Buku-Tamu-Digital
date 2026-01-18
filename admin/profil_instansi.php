<?php
/*
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php'; // Sesuaikan path jika berbeda

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$page_title = "Pengaturan Profil Kementerian Haji dan Umrah";
$message = '';
$message_type = ''; // 'success' atau 'danger'

// Ensure no residual session messages are interfering
if (isset($_SESSION['message'])) unset($_SESSION['message']);
if (isset($_SESSION['message_type'])) unset($_SESSION['message_type']);

// Path untuk menyimpan gambar yang diupload
$upload_dir = __DIR__ . '/images/'; // Pastikan direktori ini ada dan writable

// Fetch current profile data
$profile = null;
$sql_select = "SELECT * FROM tb_profile LIMIT 1";
$result = $koneksi->query($sql_select);
if ($result && $result->num_rows > 0) {
    $profile = $result->fetch_assoc();
} else {
    // Jika tidak ada profil, bisa set default atau berikan pesan error
    // Untuk saat ini, kita anggap profil sudah ada (diinsert saat pembuatan tabel)
    $message = "Data profil tidak ditemukan. Silakan hubungi administrator.";
    $message_type = "danger";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $profile) { // Hanya proses jika profil ada
    $id_profile = $profile['id_profile'];
    $nama_perusahaan = trim($_POST['nama_perusahaan']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);
    $email = trim($_POST['email']);
    $website = trim($_POST['website']);
    $deskripsi_singkat = trim($_POST['deskripsi_singkat']);
    $visi = trim($_POST['visi']);
    $misi = trim($_POST['misi']);

    // Current image filenames
    $current_foto = $profile['foto'];
    $current_foto2 = $profile['foto2'];

    $new_foto_filename = $current_foto;
    $new_foto2_filename = $current_foto2;

    // Handle file upload untuk 'foto' (logo)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $foto_tmp_name = $_FILES['foto']['tmp_name'];
        $foto_name = basename($_FILES['foto']['name']);
        $foto_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        if (in_array($foto_ext, $allowed_ext)) {
            // Buat nama file unik untuk mencegah penimpaan
            $new_foto_filename = "logo_" . uniqid() . "." . $foto_ext;
            if (move_uploaded_file($foto_tmp_name, $upload_dir . $new_foto_filename)) {
                // Hapus foto lama jika ada dan bukan default, dan nama file baru berbeda
                if ($current_foto && $current_foto != 'default-logo.png' && file_exists($upload_dir . $current_foto) && $current_foto != $new_foto_filename) {
                    unlink($upload_dir . $current_foto);
                }
            } else {
                $message = "Gagal mengupload file logo baru.";
                $message_type = "danger";
                $new_foto_filename = $current_foto; // Kembalikan ke nama lama jika gagal upload
            }
        } else {
            $message = "Format file logo tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP, SVG.";
            $message_type = "danger";
        }
    }

    // Handle file upload untuk 'foto2' (ilustrasi)
    if (isset($_FILES['foto2']) && $_FILES['foto2']['error'] == UPLOAD_ERR_OK) {
        $foto2_tmp_name = $_FILES['foto2']['tmp_name'];
        $foto2_name = basename($_FILES['foto2']['name']);
        $foto2_ext = strtolower(pathinfo($foto2_name, PATHINFO_EXTENSION));
        // $allowed_ext sudah didefinisikan di atas

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif']; // Tambahkan ini
if (in_array($ext, $allowed_ext)) {
    // lanjut proses upload
}

            $new_foto2_filename = "illust_" . uniqid() . "." . $foto2_ext;
            if (move_uploaded_file($foto2_tmp_name, $upload_dir . $new_foto2_filename)) {
                if ($current_foto2 && $current_foto2 != 'default-image.png' && file_exists($upload_dir . $current_foto2) && $current_foto2 != $new_foto2_filename) {
                    unlink($upload_dir . $current_foto2);
                }
            } else {
                $message = "Gagal mengupload file ilustrasi baru.";
                $message_type = "danger";
                $new_foto2_filename = $current_foto2;
            }
        } else {
            $message = "Format file ilustrasi tidak diizinkan. Hanya JPG, JPEG, PNG, GIF, WEBP, SVG.";
            $message_type = "danger";
        }
    }


    if ($message_type !== 'danger') { // Lanjutkan update jika tidak ada error upload sebelumnya
        $sql_update = "UPDATE tb_profile SET 
                        nama_perusahaan = ?, 
                        alamat = ?, 
                        telepon = ?, 
                        email = ?, 
                        website = ?, 
                        foto = ?, 
                        foto2 = ?, 
                        deskripsi_singkat = ?, 
                        visi = ?, 
                        misi = ?
                      WHERE id_profile = ?";
        
        if ($stmt = $koneksi->prepare($sql_update)) {
            $stmt->bind_param("ssssssssssi", 
                $nama_perusahaan, 
                $alamat, 
                $telepon, 
                $email, 
                $website, 
                $new_foto_filename, 
                $new_foto2_filename, 
                $deskripsi_singkat, 
                $visi, 
                $misi, 
                $id_profile
            );

            if ($stmt->execute()) {
                $message = "Profil instansi berhasil diperbarui.";
                $message_type = "success";
                // Re-fetch data untuk menampilkan yang terbaru di form
                $result = $koneksi->query($sql_select);
                if ($result && $result->num_rows > 0) {
                    $profile = $result->fetch_assoc();
                }
            } else {
                $message = "Gagal memperbarui profil instansi: " . $stmt->error;
                $message_type = "danger";
            }
            $stmt->close();
        } else {
            $message = "Gagal menyiapkan statement update: " . $koneksi->error;
            $message_type = "danger";
        }
    }


// $koneksi->close(); // Moved to bottom
?>
<!DOCTYPE html>
<html lang="id">
<?php include '_partials/head.php'; ?>
<body>
    <?php include_once '_partials/navbar.php'; ?>
    <?php include_once '_partials/sidebar.php'; ?>

    <main class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if ($profile): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary-gradient text-white">
                        <h5 class="mb-0">Detail Instansi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_perusahaan" class="form-label">Nama Instansi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" value="<?php echo htmlspecialchars($profile['nama_perusahaan']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2"><?php echo htmlspecialchars($profile['alamat'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi_singkat" class="form-label">Deskripsi Singkat (untuk halaman utama)</label>
                            <textarea class="form-control" id="deskripsi_singkat" name="deskripsi_singkat" rows="2"><?php echo htmlspecialchars($profile['deskripsi_singkat'] ?? ''); ?></textarea>
                        </div>
                         <div class="mb-3">
                            <label for="visi" class="form-label">Visi</label>
                            <textarea class="form-control" id="visi" name="visi" rows="3"><?php echo htmlspecialchars($profile['visi'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="misi" class="form-label">Misi</label>
                            <textarea class="form-control" id="misi" name="misi" rows="3"><?php echo htmlspecialchars($profile['misi'] ?? ''); ?></textarea>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Gambar</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="foto" class="form-label">Logo Instansi (Max 2MB)</label>
                                <input class="form-control" type="file" id="foto" name="foto" accept="image/*">
                                <?php if (!empty($profile['foto'])): ?>
                                    <div class="mt-2">
                                        <small>Logo Saat Ini:</small><br>
                                        <img src="images/<?php echo htmlspecialchars($profile['foto']); ?>" alt="Logo Saat Ini" class="current-image">
                                    </div>
                                <?php endif; ?>
                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah logo.</small>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary-custom"><i class="bi bi-save-fill"></i> Simpan Perubahan</button>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <?php if(empty($message)) : // Hanya tampilkan jika belum ada pesan error spesifik dari fetch ?>
                <div class="alert alert-warning" role="alert">
                    Data profil perusahaan belum ada atau tidak dapat dimuat. Silakan pastikan data awal sudah dimasukkan ke tabel <code>tb_profile</code>.
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php if (isset($koneksi)) $koneksi->close(); ?>