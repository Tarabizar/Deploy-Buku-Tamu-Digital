<?php
// File: admin/_partials/navbar.php
// Pastikan session sudah dimulai di halaman yang memanggil partial ini
$admin_nama_display = htmlspecialchars($_SESSION['admin_nama_lengkap'] ?? ($_SESSION['admin_username'] ?? 'Admin'));
?>
<nav class="navbar navbar-expand-md navbar-admin fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-md-none" href="index.php">
            <i class="bi bi-kaaba-fill text-primary"></i> Kementerian Haji & Umrah
        </a>
        <button class="navbar-toggler border-0" type="button" id="sidebarToggleBtn" aria-label="Toggle navigation">
            <i class="bi bi-list text-primary" style="font-size: 1.5rem;"></i>
        </button>
        
        <div class="collapse navbar-collapse" id="adminNavbar"> 
            
            <!-- Admin Clock -->
            <!-- Admin Clock -->
            <div class="mx-auto d-none d-lg-block">
                <div id="adminClock" class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3" 
                     style="background: rgba(93, 64, 55, 0.1); border: 1px solid rgba(93, 64, 55, 0.2); color: var(--primary-color);">
                    <i class="bi bi-clock-fill"></i>
                    <span class="time fw-bold" style="font-family: 'Poppins', sans-serif; letter-spacing: 1px;">Loading...</span>
                </div>
            </div>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 32px; height: 32px; border: 1px solid var(--secondary-color);">
                            <?php echo strtoupper(substr($admin_nama_display, 0, 1)); ?>
                        </div>
                        <span class="d-none d-md-inline text-dark"><?php echo $admin_nama_display; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow" aria-labelledby="adminDropdown" style="margin-top: 10px;">
                        <li><h6 class="dropdown-header">Akun Admin</h6></li>
                        <li><a class="dropdown-item" href="profil_saya.php"><i class="bi bi-person-badge me-2 text-secondary"></i>Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
    function updateAdminClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const timeString = `${hours}:${minutes}:${seconds}`;
        
        const clockElement = document.querySelector('#adminClock .time');
        if (clockElement) {
            clockElement.textContent = timeString;
        }
    }
    setInterval(updateAdminClock, 1000);
    updateAdminClock(); // Run immediately
</script>