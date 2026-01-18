<?php
// File: admin/_partials/sidebar.php
// Menentukan halaman aktif untuk styling link di sidebar
$current_page = basename($_SERVER['PHP_SELF']);
$base_url_admin = "."; // Path relatif ke halaman admin dari dalam folder admin
?>
<div class="sidebar" id="adminSidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-center">
        <?php
        $logo_path_sidebar = '';
        if (isset($koneksi)) {
            $sql_sidebar = "SELECT foto FROM tb_profile LIMIT 1";
            $res_sidebar = $koneksi->query($sql_sidebar);
            if ($res_sidebar && $res_sidebar->num_rows > 0) {
                $row_sidebar = $res_sidebar->fetch_assoc();
                if (!empty($row_sidebar['foto']) && file_exists(__DIR__ . '/../images/' . $row_sidebar['foto'])) {
                     $logo_path_sidebar = 'images/' . $row_sidebar['foto'];
                }
            }
        }
        
        if (!empty($logo_path_sidebar)): ?>
            <img src="<?php echo htmlspecialchars($logo_path_sidebar); ?>" alt="Logo" class="me-2" style="max-height: 45px; width: auto;">
        <?php else: ?>
            <i class="bi bi-kaaba-fill me-2 fs-4 text-secondary-custom"></i>
        <?php endif; ?>
        <h5 class="mb-0">Haji & Umrah</h5>
    </div>
    <ul class="nav flex-column mt-2">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo $base_url_admin; ?>/index.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <!-- <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'profil_instansi.php') ? 'active' : ''; ?>" href="<?php echo $base_url_admin; ?>/profil_instansi.php">
                <i class="bi bi-buildings-fill"></i> Profil Instansi
            </a> -->
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'data_tamu.php' || $current_page == 'detail_tamu.php' || $current_page == 'edit_tamu.php') ? 'active' : ''; ?>" href="<?php echo $base_url_admin; ?>/data_tamu.php">
                <i class="bi bi-people-fill"></i> Data Tamu
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'data_kepuasan.php') ? 'active' : ''; ?>" href="<?php echo $base_url_admin; ?>/data_kepuasan.php">
                <i class="bi bi-patch-check-fill"></i> Data Kepuasan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manajemen_admin.php' || $current_page == 'tambah_admin.php') ? 'active' : ''; ?>" href="<?php echo $base_url_admin; ?>/manajemen_admin.php">
                <i class="bi bi-shield-lock-fill"></i> Manajemen Admin
            </a>
        </li>
        
        <li class="nav-item mt-auto p-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
            <a class="nav-link text-white-50" href="../index.php" target="_blank">
                <i class="bi bi-box-arrow-up-right"></i> Lihat Website
            </a>
        </li>
    </ul>
</div>