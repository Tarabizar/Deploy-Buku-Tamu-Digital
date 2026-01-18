<?php
declare(strict_types=1);
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
// Pastikan file koneksi.php ada dan dapat diakses
// Sebaiknya gunakan path absolut jika memungkinkan atau pastikan path relatif sudah benar
$koneksiPath = __DIR__ . DIRECTORY_SEPARATOR . "koneksi" . DIRECTORY_SEPARATOR . "koneksi.php";
if (file_exists($koneksiPath)) {
    require_once $koneksiPath;
} else {
    // Penanganan jika file koneksi tidak ditemukan
    // Bisa redirect ke halaman error, atau menampilkan pesan
    die("File koneksi database tidak ditemukan. Harap periksa konfigurasi.");
}

date_default_timezone_set('Asia/Jakarta');

// Fetch company profile data
$profile = null;
if (isset($koneksi) && $koneksi instanceof mysqli) { // Pastikan $koneksi adalah objek mysqli yang valid
    $result = $koneksi->query("SELECT * FROM tb_profile LIMIT 1");
    if ($result) {
        $profile = $result->fetch_assoc();
    } else {
        // Penanganan error query, misalnya log error
        // error_log("Gagal mengambil profil perusahaan: " . $koneksi->error);
        // Tetapkan nilai default jika query gagal
        $profile = ['nama_perusahaan' => 'Perusahaan Default', 'foto' => 'default-logo.png', 'foto2' => 'default-image.png'];
    }
} else {
    // Penanganan jika koneksi gagal atau $koneksi bukan objek mysqli
    // error_log("Koneksi database gagal atau tidak valid.");
    $profile = ['nama_perusahaan' => 'Perusahaan Default (Koneksi Gagal)', 'foto' => 'default-logo.png', 'foto2' => 'default-image.png'];
}


$currentDay = mktime(0, 0, 0, (int)date("n"), (int)date("j"), (int)date("Y"));

function tglIndonesia(string $str): string {
    $translations = [
        'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa',
        'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jum\'at',
        'Sat' => 'Sabtu', 'January' => 'Januari', 'February' => 'Februari',
        'March' => 'Maret', 'April' => 'April', 'May' => 'Mei',
        'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus',
        'September' => 'September', 'October' => 'Oktober',
        'November' => 'November', 'December' => 'Desember'
    ];
    return strtr(trim($str), $translations);
}

$currentPage = $_GET['page'] ?? 'beranda';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu Kementerian Haji & Umrah Kabupaten Karawang - <?= htmlspecialchars($profile['nama_perusahaan'] ?? 'Perusahaan') ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>


    <style>
        :root {
            /* Palet Warna Gradasi Cerah Baru */
            /* Palet Warna Gradasi Coklat Emas (Hajj & Umrah Style) */
            --bright-gradient-start: #C19A6B; /* Camel / Light Brown */
            --bright-gradient-end: #5D4037;   /* Dark Brown */
            --bright-gradient-alt-start: #D4AF37; /* Gold */
            --bright-gradient-alt-end: #AA6C39;   /* Bronze */

            --primary-color: #5D4037; /* Dark Brown sebagai warna primer solid */
            --accent-color: #D4AF37;  /* Gold sebagai aksen */

            /* Warna Netral Modern */
            --neutral-lightest: #F8F9FA; /* Sangat terang, hampir putih */
            --neutral-lighter: #E9ECEF;  /* Abu-abu terang */
            --neutral-light: #DEE2E6;   /* Abu-abu sedikit lebih gelap */
            --neutral-medium: #CED4DA;  /* Abu-abu sedang */
            --neutral-dark: #495057;     /* Abu-abu gelap untuk teks */
            --neutral-darker: #343A40;   /* Abu-abu sangat gelap */
            --neutral-darkest: #212529;  /* Hampir hitam */

            /* Gradien Utama */
            --gradient-main: linear-gradient(135deg, var(--bright-gradient-start) 0%, var(--bright-gradient-end) 100%);
            --gradient-accent: linear-gradient(135deg, var(--bright-gradient-alt-start) 0%, var(--bright-gradient-alt-end) 100%);
            --gradient-border: linear-gradient(90deg, transparent, var(--primary-color), transparent);

            /* Bayangan yang Ditingkatkan */
            --shadow-soft: 0 4px 15px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 12px 35px rgba(0, 0, 0, 0.12);

            /* Radius Sudut yang Konsisten */
            --radius-sm: 0.375rem; /* 6px */
            --radius-md: 0.75rem;  /* 12px */
            --radius-lg: 1.25rem;  /* 20px */
            --radius-xl: 2rem;    /* 32px */
            --radius-full: 9999px;

            /* Transisi */
            --transition-fast: all 0.2s ease-in-out;
            --transition-medium: all 0.35s ease-in-out;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--neutral-lightest);
            color: var(--neutral-darker);
            line-height: 1.7;
            font-weight: 400;
        }

        /* Struktur Layout */
        .app-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden; /* Mencegah scroll horizontal yang tidak diinginkan */
        }

        /* Header Modern */
        .app-header {
            background: rgba(255, 255, 255, 0.8); /* Efek glassmorphism tipis */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: var(--shadow-soft);
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--neutral-light);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem; /* Mengurangi gap sedikit */
        }

        .company-logo {
            width: 40px; /* Sedikit lebih kecil */
            height: 40px;
            object-fit: contain;
            border-radius: var(--radius-md);
            border: 2px solid var(--primary-color);
            box-shadow: var(--shadow-soft);
            transition: var(--transition-medium);
        }
        .company-logo:hover {
            transform: scale(1.1) rotate(-3deg);
            box-shadow: var(--shadow-medium);
        }
        .company-title h1 {
            font-size: 1.15rem; /* Menyesuaikan ukuran font */
            font-weight: 700;
            color: var(--primary-color);
        }
        .company-title small {
            font-size: 0.75rem;
            color: var(--neutral-dark);
            font-weight: 500;
        }

        /* Tombol Menu Canggih */
        .menu-actions .btn {
            border-radius: var(--radius-full);
            padding: 0.4rem 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: var(--transition-medium);
            box-shadow: var(--shadow-soft);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .menu-actions .btn-gradient {
            background: var(--gradient-main);
            color: white;
            border: none;
        }
        .menu-actions .btn-gradient:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: var(--shadow-medium);
            filter: brightness(1.1);
        }
        .menu-actions .btn-outline-dynamic {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        .menu-actions .btn-outline-dynamic:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px) scale(1.03);
            box-shadow: var(--shadow-medium);
        }


        /* Konten Utama */
        .main-content {
            flex: 1;
            padding: 2.5rem 0;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem; /* Sedikit lebih banyak ruang */
        }

        @media (min-width: 992px) {
            .content-grid {
                grid-template-columns: 2fr 3fr; /* Proporsi baru, form lebih lebar */
            }
        }

        /* Bagian Selamat Datang yang Ditingkatkan */
        .welcome-section {
            background: var(--gradient-main);
            color: white;
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-strong);
        }
        .welcome-section::before { /* Efek latar belakang halus */
            content: '';
            position: absolute;
            top: -20%; right: -20%;
            width: 150%; height: 150%;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.07'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            animation: bgPatternScroll 45s linear infinite;
            z-index: 0;
        }
        @keyframes bgPatternScroll {
            0% {transform: translate(0, 0);}
            100% {transform: translate(-50px, -50px);}
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .welcome-content .badge {
            padding: 0.5em 1em;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        .welcome-content .display-5 { font-weight: 700; }
        .welcome-content p { opacity: 0.9; font-weight: 300;}

        .modern-divider {
            height: 2px;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.5) 50%, rgba(255,255,255,0) 100%);
            margin: 2rem 0;
            border: none;
        }

        .company-info-welcome h3 { font-weight: 600; margin-bottom: 1rem;}


        /* Bagian Formulir yang Ditingkatkan */
        .form-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--neutral-lighter);
            transition: var(--transition-medium);
        }
        .form-section:hover {
            box-shadow: var(--shadow-strong);
        }

        .section-header {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 2rem; /* Lebih banyak spasi */
        }
        .section-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 70px; /* Lebih panjang */
            height: 4px; /* Lebih tebal */
            background: var(--gradient-accent);
            border-radius: var(--radius-full);
        }
        .section-header h2 {
            font-weight: 700;
            color: var(--primary-color); /* Menggunakan warna primer */
        }

        /* Elemen Formulir Modern */
        .form-control, .form-select, .select2-container--bootstrap-5 .select2-selection {
            border-radius: var(--radius-sm);
            padding: 0.85rem 1.1rem; /* Sedikit lebih besar */
            border: 1px solid var(--neutral-light);
            transition: var(--transition-fast);
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            background-color: var(--neutral-lightest); /* Latar belakang yang sangat terang */
        }
        .form-control:focus, .form-select:focus,
        .select2-container--bootstrap-5 .select2-selection--single:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(var(--rgb-primary-color, 14, 92, 173), 0.25); /* Menggunakan RGB untuk shadow */
            background-color: white;
        }
        /* Untuk Select2 agar placeholder terlihat */
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--neutral-dark);
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--neutral-light);
        }
        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--primary-color) !important; /* gradien bisa terlalu ramai disini */
            color: white !important;
        }

        /* Tombol Utama yang Ditingkatkan */
        .btn-submit-modern {
            background: var(--gradient-main); /* Gradien cerah utama */
            border: none;
            border-radius: var(--radius-md); /* Sudut lebih bulat */
            padding: 0.85rem 2rem;
            font-weight: 600;
            letter-spacing: 0.8px;
            color: white;
            transition: var(--transition-medium);
            box-shadow: var(--shadow-soft);
            text-transform: uppercase;
            position: relative;
            overflow: hidden;
        }
        .btn-submit-modern:before { /* Efek kilau saat hover */
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.6s ease;
        }
        .btn-submit-modern:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: var(--shadow-strong);
            filter: brightness(1.15);
        }
        .btn-submit-modern:hover:before {
            left: 100%;
        }
        .btn-submit-modern:active {
            transform: translateY(-1px) scale(0.98);
            box-shadow: var(--shadow-soft);
        }


        /* Tampilan Jam yang Ditingkatkan */
        .clock-info {
            text-align: right;
        }
        .date-display {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--neutral-dark);
            margin-bottom: 0.25rem;
        }
        .clock-display {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(var(--rgb-primary-color, 14, 92, 173), 0.1);
            padding: 0.6rem 1rem;
            border-radius: var(--radius-sm);
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--primary-color);
            border: 1px solid rgba(var(--rgb-primary-color, 14, 92, 173), 0.2);
        }
        .clock-display .bi-clock { font-size: 1.1rem; }
        .clock-display .time { font-size: 1rem; letter-spacing: 0.5px;}


        /* Animasi */
        .animate-float-enhanced {
            animation: floatEnhanced 8s ease-in-out infinite;
        }
        @keyframes floatEnhanced {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px) rotate(2deg); }
            100% { transform: translateY(0px); }
        }

        /* Penyesuaian Responsif */
        @media (max-width: 991.98px) {
            .content-grid {
                grid-template-columns: 1fr; /* Kembali ke satu kolom di tablet */
            }
            .welcome-section {
                min-height: auto; /* Hapus tinggi minimum agar konten bisa menentukan tinggi */
                padding: 2rem;
            }
            .form-section {
                padding: 2rem;
            }
             .welcome-content .display-5 { font-size: 2.25rem; }
        }

        @media (max-width: 767.98px) {
            .header-content {
                flex-direction: column;
                align-items: center; /* Pusatkan item di mobile */
                gap: 1rem;
            }
            .logo-container {
                flex-direction: column;
                text-align: center;
            }
            .company-title h1 { font-size: 1.1rem; }
            .company-title small { font-size: 0.75rem; }

            .clock-info { text-align: center; margin-top: 0.5rem; }

            .main-content { padding: 1.5rem 0; }
            .welcome-section, .form-section { padding: 1.5rem; }
            .section-header::after { width: 50px; height: 3px; }
            .btn-submit-modern, .menu-actions .btn { padding: 0.7rem 1.5rem; font-size: 0.9rem;}
        }

        /* Footer Modern */
        .app-footer {
            padding: 1.5rem 0;
            background-color: var(--neutral-darkest);
            color: var(--neutral-lighter);
            text-align: center;
            font-size: 0.875rem;
        }
        .app-footer p { margin-bottom: 0; }
        .app-footer a {
            color: var(--bright-gradient-start);
            text-decoration: none;
            font-weight: 500;
        }
        .app-footer a:hover {
            color: white;
            text-decoration: underline;
        }

        /* Utility Helpers */
        .pointer-events-none { pointer-events: none; }
        .backdrop-blur { 
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }
    </style>
</head>
<body class="app-container">
    <!-- Floating Mobile Toggle Button with Premium Theme -->
    <button id="burgerToggle" class="btn shadow-lg d-lg-none animate__animated animate__fadeInRight" 
            style="position: fixed; top: 15px; right: 15px; z-index: 9999; width: 50px; height: 50px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 2px solid white; background: var(--gradient-main); color: white;">
        <i class="bi bi-list fs-3"></i>
    </button>
    
    <header class="app-header" id="appHeader">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo-container text-decoration-none">
                    <img src="admin/images/<?= htmlspecialchars($profile['foto'] ?? 'default-logo.png') ?>"
                         alt="Company Logo" class="company-logo">
                    <div class="company-title">
                        <h1 class="mb-0" style="line-height: 1.2;">Kementerian Haji dan Umrah</h1>
                        <small style="font-size: 0.85rem; opacity: 0.9;">Kabupaten Karawang</small>
                    </div>
                </a>

                <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                    <div class="clock-info d-none d-md-block">
                        <div class="date-display"><?= tglIndonesia(date('D, d F Y', $currentDay)) ?></div>
                        <div id="clock" class="clock-display">
                            <i class="bi bi-clock"></i>
                            <span class="time"></span>
                        </div>
                    </div>

                    <div class="menu-actions d-flex gap-2 gap-sm-3">
                        <a href="admin/login.php" class="btn btn-gradient">
                            <i class="bi bi-shield-lock"></i>
                            <span class="d-none d-sm-inline">Login Admin</span>
                        </a>
                        <?php if ($currentPage === "spk"): ?>
                            <a href="index.php" class="btn btn-outline-dynamic">
                                <i class="bi bi-person-plus-fill"></i>
                                <span class="d-none d-sm-inline">Register Tamu</span>
                            </a>
                        <?php else: ?>
                            <a href="?page=spk" class="btn btn-gradient">
                                <i class="bi bi-star-fill"></i>
                                <span class="d-none d-sm-inline">Indeks Kepuasan</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
             <div class="clock-info d-block d-md-none mt-3">
                <div class="date-display"><?= tglIndonesia(date('D, d F Y', $currentDay)) ?></div>
                <div id="clock-mobile" class="clock-display justify-content-center">
                    <i class="bi bi-clock"></i>
                    <span class="time-mobile"></span>
                </div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="content-grid">
                <section id="welcomeSection" class="welcome-section animate__animated animate__fadeInLeft p-4 p-md-5 position-relative overflow-hidden">
                    <!-- Decorative Background Icon -->
                    <div class="position-absolute top-50 start-50 translate-middle pointer-events-none" style="opacity: 0.05; z-index: 0;">
                        <i class="bi bi-building" style="font-size: 30rem; color: white;"></i>
                    </div>

                    <div class="welcome-content h-100 d-flex flex-column justify-content-center position-relative" style="z-index: 1;">
                        <div class="text-center text-lg-start">
                            <span class="badge bg-white text-dark shadow-sm px-3 py-2 mb-4 fs-6">
                                <i class="bi bi-stars text-warning me-1"></i> Selamat Datang
                            </span>
                            
                            <h2 class="display-5 fw-bold mb-3 text-white">Buku Tamu Digital</h2>
                            <h3 class="h2 mb-4 fw-light text-white-50 lh-base">
                                Kementerian Haji dan Umrah <br>
                                <strong class="text-white">Kabupaten Karawang</strong>
                            </h3>
                            
                            <p class="lead mb-5 opacity-90 fw-light" style="max-width: 90%;">
                                Kami menyambut Anda dengan sepenuh hati. Silakan isi data diri Anda pada formulir untuk keperluan dokumentasi dan pelayanan yang lebih baik.
                            </p>
                        </div>

                        <div class="row g-3 mt-auto">
                            <div class="col-6">
                                <div class="bg-white bg-opacity-10 p-3 rounded-4 border border-white border-opacity-25 h-100 text-center backdrop-blur">
                                    <i class="bi bi-emoji-smile fs-1 mb-2 d-block text-warning"></i>
                                    <h6 class="mb-1 text-white">Pelayanan Prima</h6>
                                    <small class="text-white-50">Ramah & Cepat</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white bg-opacity-10 p-3 rounded-4 border border-white border-opacity-25 h-100 text-center backdrop-blur">
                                    <i class="bi bi-shield-check fs-1 mb-2 d-block text-warning"></i>
                                    <h6 class="mb-1 text-white">Data Aman</h6>
                                    <small class="text-white-50">Terjamin Kerahasiaannya</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="form-section animate__animated animate__fadeInRight">
                    <div class="section-header">
                        <h2 class="h3 mb-0">
                            <?= $currentPage === "spk" ? 'Formulir Kepuasan Layanan' : 'Registrasi Kunjungan Tamu' ?>
                        </h2>
                    </div>

                    <?php
                    // Pastikan file isi.php ada
                    $isiPath = __DIR__ . DIRECTORY_SEPARATOR . "isi.php";
                    if (file_exists($isiPath)) {
                        include $isiPath;
                    } else {
                        echo '<div class="alert alert-warning" role="alert">Konten formulir (isi.php) tidak ditemukan.</div>';
                    }
                    ?>
                </section>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="container">
            <p>&copy; 2026 Kementerian Haji & Umrah Kabupaten Karawang</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Inisialisasi RGB color untuk CSS variable (digunakan di box-shadow focus)
        function hexToRgb(hex) {
            let r = 0, g = 0, b = 0;
            // 3 digits
            if (hex.length == 4) {
                r = "0x" + hex[1] + hex[1];
                g = "0x" + hex[2] + hex[2];
                b = "0x" + hex[3] + hex[3];
            // 6 digits
            } else if (hex.length == 7) {
                r = "0x" + hex[1] + hex[2];
                g = "0x" + hex[3] + hex[4];
                b = "0x" + hex[5] + hex[6];
            }
            return `${+r},${+g},${+b}`;
        }
        const primaryColorHex = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim();
        document.documentElement.style.setProperty('--rgb-primary-color', hexToRgb(primaryColorHex));


        // Enhanced Digital Clock
        function updateClock() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;

            const clockElement = document.querySelector('#clock .time');
            if (clockElement) clockElement.textContent = timeString;

            const clockMobileElement = document.querySelector('#clock-mobile .time-mobile');
            if (clockMobileElement) clockMobileElement.textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        $(document).ready(function() {
            // Initialize Select2 with modern styling
            if ($('.select2').length) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Pilih salah satu...',
                    // dropdownParent: $('.form-section') // Sesuaikan jika select2 ada di dalam modal atau elemen lain
                });
            }

            // Chain select functionality (jika masih digunakan)
            // Pastikan jquery.chained.js sudah dimuat dan elemennya ada
            if (typeof $.fn.chained !== 'undefined' && $("#pegawai").length && $("#unit_kerja").length) {
                $("#pegawai").chained("#unit_kerja");
            } else if ($("#pegawai").length && $("#unit_kerja").length) {
                console.warn("jquery.chained.js tidak dimuat atau elemen select tidak ditemukan.");
            }

            // SweetAlert untuk notifikasi (contoh)
            // Anda bisa trigger ini dari PHP setelah form submission
            <?php
            if (isset($_SESSION['sukses'])) {
                $pesanSukses = $_SESSION['sukses'];
                unset($_SESSION['sukses']);
                echo "Swal.fire({
                        title: 'Berhasil!',
                        text: " . json_encode($pesanSukses) . ",
                        icon: 'success',
                        confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim(),
                        timer: 3000
                      });";
            }
            if (isset($_SESSION['gagal'])) {
                $pesanGagal = $_SESSION['gagal'];
                unset($_SESSION['gagal']);
                 echo "Swal.fire({
                        title: 'Gagal!',
                        text: " . json_encode($pesanGagal) . ",
                        icon: 'error',
                        confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--accent-color').trim(),
                        timer: 3000
                      });";
            }
            ?>

            // Tooltip Bootstrap (jika ada elemen dengan data-bs-toggle="tooltip")
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        });

        // Toggle Header on Mobile (Burger Menu Premium)
        document.getElementById('burgerToggle')?.addEventListener('click', function() {
            const appHeader = document.getElementById('appHeader');
            // User requested the icon always remains "Burger" (bi-list)
            
            if (appHeader.classList.contains('d-none')) {
                // Show Header
                appHeader.classList.remove('d-none');
                // Icon stays burger
            } else {
                // Hide Header
                appHeader.classList.add('d-none');
                // Icon stays burger
            }
        });

        // Efek smooth scroll untuk anchor link jika ada
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if (this.getAttribute('href').length > 1 && document.querySelector(this.getAttribute('href'))) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

    </script>
</body>
</html>
