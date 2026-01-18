<?php
session_start();

// Cek apakah admin sudah login, jika tidak, redirect ke halaman login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Memanggil file koneksi DULUAN, karena kita butuh $koneksi
require_once __DIR__ . '/../koneksi/koneksi.php'; // Pastikan path ini benar

// Mengambil nama admin dari session untuk ditampilkan
$admin_nama = htmlspecialchars($_SESSION['admin_nama_lengkap'] ?? ($_SESSION['admin_username'] ?? 'Admin'));
$page_title = "Dashboard Admin";

// Inisialisasi variabel statistik
$total_tamu_hari_ini = 0;
$total_survei_hari_ini = 0;
$total_tamu_bulan_ini = 0;
$total_tamu_keseluruhan = 0;

// Pastikan $koneksi ada dan merupakan objek mysqli yang valid
if (isset($koneksi) && $koneksi instanceof mysqli) {

    // 1. Total Tamu Hari Ini
    $sql_tamu_today = "SELECT COUNT(*) as total FROM tb_tamu WHERE tanggal_kunjungan = CURDATE()";
    $result_tamu_today = $koneksi->query($sql_tamu_today);
    if ($result_tamu_today) {
        $total_tamu_hari_ini = $result_tamu_today->fetch_assoc()['total'] ?? 0;
    } else {
        error_log("Error query total_tamu_hari_ini: " . $koneksi->error);
    }

    // 2. Total Survei Hari Ini
    $sql_survei_today = "SELECT COUNT(*) as total FROM tb_kepuasan WHERE tanggal_survei = CURDATE()";
    $result_survei_today = $koneksi->query($sql_survei_today);
    if ($result_survei_today) {
        $total_survei_hari_ini = $result_survei_today->fetch_assoc()['total'] ?? 0;
    } else {
        error_log("Error query total_survei_hari_ini: " . $koneksi->error);
    }

    // 3. Total Tamu Bulan Ini
    $sql_tamu_month = "SELECT COUNT(*) as total FROM tb_tamu WHERE MONTH(tanggal_kunjungan) = MONTH(CURDATE()) AND YEAR(tanggal_kunjungan) = YEAR(CURDATE())";
    $result_tamu_month = $koneksi->query($sql_tamu_month);
    if ($result_tamu_month) {
        $total_tamu_bulan_ini = $result_tamu_month->fetch_assoc()['total'] ?? 0;
    } else {
        error_log("Error query total_tamu_bulan_ini: " . $koneksi->error);
    }

    // 4. Total Tamu Keseluruhan
    $sql_tamu_all = "SELECT COUNT(*) as total FROM tb_tamu";
    $result_tamu_all = $koneksi->query($sql_tamu_all);
    if ($result_tamu_all) {
        $total_tamu_keseluruhan = $result_tamu_all->fetch_assoc()['total'] ?? 0;
    } else {
        error_log("Error query total_tamu_keseluruhan: " . $koneksi->error);
    }

    // $koneksi->close(); // Tidak perlu ditutup di sini jika masih ada potensi penggunaan di partials atau bagian lain.
                       // PHP akan menutupnya otomatis.
} else {
    // Handle jika $koneksi tidak tersedia (seharusnya tidak terjadi jika require_once berhasil)
    error_log("Variabel koneksi tidak tersedia atau bukan instance mysqli di admin/index.php");
    // Anda bisa set pesan error di sini jika mau
}

?>
<!DOCTYPE html>
<html lang="id">
<?php include '_partials/head.php'; ?>
<body>
    <?php
    // Memastikan partials ada sebelum di-include
    if (file_exists(__DIR__ . '/_partials/navbar.php')) {
        include_once __DIR__ . '/_partials/navbar.php';
    } else {
        echo '<nav class="navbar navbar-light bg-light fixed-top"><div class="container-fluid"><span class="navbar-brand mb-0 h1">Error: Navbar partial (_partials/navbar.php) tidak ditemukan.</span></div></nav>';
    }

    if (file_exists(__DIR__ . '/_partials/sidebar.php')) {
        include_once __DIR__ . '/_partials/sidebar.php';
    } else {
        echo '<div style="width:0; height:0;"></div>';
        error_log("Peringatan: Sidebar partial (_partials/sidebar.php) tidak ditemukan di admin/index.php");
    }
    ?>

    <main class="main-content">
        <div class="container-fluid">
            <!-- Header Section -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                <div>
                    <h1 class="h2 page-title mb-0"><?php echo htmlspecialchars($page_title); ?></h1>
                    <p class="text-muted small mb-0">Selamat datang kembali, <strong><?php echo htmlspecialchars($admin_nama); ?></strong></p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3"></i>
                        <?php echo date('d M Y'); ?>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-5">
                <!-- Card 1: Tamu Hari Ini (Deep Green) -->
                <div class="col-lg-3 col-md-6">
                    <div class="card card-admin-stat bg-primary-gradient h-100 shadow-sm position-relative">
                        <div class="card-body">
                            <i class="bi bi-people-fill stat-icon"></i>
                            <div class="stat-label mb-1">Tamu Hari Ini</div>
                            <div class="stat-value"><?php echo $total_tamu_hari_ini; ?></div>
                            <small class="text-white-50">Pengunjung Terdata</small>
                        </div>
                        <a href="data_tamu.php" class="card-footer bg-transparent border-top-0 text-white text-decoration-none small d-flex justify-content-between align-items-center">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Card 2: Survei Hari Ini (Gold) -->
                <div class="col-lg-3 col-md-6">
                    <div class="card card-admin-stat bg-gold-gradient h-100 shadow-sm position-relative">
                        <div class="card-body">
                            <i class="bi bi-star-fill stat-icon"></i>
                            <div class="stat-label mb-1">Survei Masuk</div>
                            <div class="stat-value"><?php echo $total_survei_hari_ini; ?></div>
                            <small class="text-white-50">Hari Ini</small>
                        </div>
                        <a href="data_kepuasan.php" class="card-footer bg-transparent border-top-0 text-white text-decoration-none small d-flex justify-content-between align-items-center">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Card 3: Tamu Bulan Ini (White w/ Border) -->
                <div class="col-lg-3 col-md-6">
                    <div class="card card-admin-stat bg-white h-100 shadow-sm border-start border-4 border-brown position-relative" style="border-color: var(--primary-color) !important;">
                        <div class="card-body">
                            <i class="bi bi-calendar-check-fill stat-icon text-primary-custom"></i>
                            <div class="stat-label mb-1 text-muted">Bulan Ini</div>
                            <div class="stat-value text-dark"><?php echo $total_tamu_bulan_ini; ?></div>
                            <small class="text-muted">Akumulasi Bulanan</small>
                        </div>
                        <a href="data_tamu.php" class="card-footer bg-transparent border-top-0 text-primary-custom text-decoration-none small d-flex justify-content-between align-items-center">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Card 4: Total Tamu (White w/ Border) -->
                <div class="col-lg-3 col-md-6">
                    <div class="card card-admin-stat bg-white h-100 shadow-sm border-start border-4 border-warning position-relative">
                        <div class="card-body">
                            <i class="bi bi-journal-album stat-icon text-warning"></i>
                            <div class="stat-label mb-1 text-muted">Total Keseluruhan</div>
                            <div class="stat-value text-dark"><?php echo $total_tamu_keseluruhan; ?></div>
                            <small class="text-muted">Sejak Awal</small>
                        </div>
                        <a href="data_tamu.php" class="card-footer bg-transparent border-top-0 text-warning text-decoration-none small d-flex justify-content-between align-items-center">
                            Lihat Detail <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-custom shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Tamu Terbaru</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php
                                // Ambil 3 tamu terakhir
                                $sql_aktivitas = "SELECT nama_tamu, keperluan, waktu_masuk, tanggal_kunjungan, asal_instansi FROM tb_tamu ORDER BY tanggal_kunjungan DESC, waktu_masuk DESC LIMIT 5";
                                $result_aktivitas = isset($koneksi) ? $koneksi->query($sql_aktivitas) : null;
                                
                                if ($result_aktivitas && $result_aktivitas->num_rows > 0) {
                                    while($aktivitas = $result_aktivitas->fetch_assoc()):
                                        $initial = strtoupper(substr($aktivitas['nama_tamu'], 0, 1));
                            ?>
                            <div class="list-group-item px-4 py-3 border-bottom-0">
                                <div class="d-flex w-100 align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 45px; height: 45px; background-color: var(--secondary-color);">
                                            <?php echo $initial; ?>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($aktivitas['nama_tamu']); ?></h6>
                                            <small class="text-muted fst-italic">
                                                <i class="bi bi-clock me-1"></i>
                                                <?php echo htmlspecialchars(date('d M, H:i', strtotime($aktivitas['tanggal_kunjungan'] . ' ' . $aktivitas['waktu_masuk']))); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1 text-muted small"><i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($aktivitas['asal_instansi']); ?></p>
                                        <p class="mb-0 small text-dark bg-light p-2 rounded border-start border-3 border-primary">
                                            "<?php echo nl2br(htmlspecialchars(mb_strimwidth($aktivitas['keperluan'], 0, 120, "..."))); ?>"
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php
                                    endwhile;
                                } else {
                                    echo '<div class="p-5 text-center text-muted"><i class="bi bi-inbox fs-1 d-block mb-3"></i>Belum ada aktivitas tamu terbaru.</div>';
                                }
                            ?>
                        </div>
                        <div class="card-footer bg-white text-center py-3">
                             <a href="data_tamu.php" class="btn btn-sm btn-outline-primary rounded-pill px-4">Lihat Semua Aktivitas</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar di mobile
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