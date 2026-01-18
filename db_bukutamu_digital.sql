-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 18, 2026 at 09:44 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_bukutamu_digital`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `id_admin` int(11) NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','superadmin') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`id_admin`, `nama_lengkap`, `username`, `password_hash`, `email`, `role`, `last_login`, `created_at`) VALUES
(3, 'Admin', 'Admin', '$2y$10$2XErrg4kAED.aM4LJWyJV.RKcL8oXd2dvYMRzxRZ2W919AnfUvPrG', 'admin@gmail.com', 'admin', '2026-01-18 08:11:22', '2025-05-09 08:03:18');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kepuasan`
--

CREATE TABLE `tb_kepuasan` (
  `id_kepuasan` int(11) NOT NULL,
  `id_tamu_fk` int(11) DEFAULT NULL,
  `nama_responden` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_survei` date NOT NULL,
  `waktu_survei` time NOT NULL,
  `nilai_pelayanan` tinyint(4) NOT NULL COMMENT 'Skala 1-5: 1=Sangat Buruk, 5=Sangat Baik',
  `komentar_pelayanan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_fasilitas` tinyint(4) NOT NULL COMMENT 'Skala 1-5',
  `komentar_fasilitas` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_keramahan` tinyint(4) NOT NULL COMMENT 'Skala 1-5',
  `komentar_keramahan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_kecepatan` tinyint(4) NOT NULL COMMENT 'Skala 1-5',
  `komentar_kecepatan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `saran_masukan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_kepuasan`
--

INSERT INTO `tb_kepuasan` (`id_kepuasan`, `id_tamu_fk`, `nama_responden`, `tanggal_survei`, `waktu_survei`, `nilai_pelayanan`, `komentar_pelayanan`, `nilai_fasilitas`, `komentar_fasilitas`, `nilai_keramahan`, `komentar_keramahan`, `nilai_kecepatan`, `komentar_kecepatan`, `saran_masukan`, `created_at`) VALUES
(5, NULL, 'wwwiuijngv', '2025-05-10', '13:49:51', 4, '', 5, '', 4, '', 4, '', 'nnnnn', '2025-05-10 06:49:51'),
(6, NULL, '', '2025-07-17', '18:37:55', 4, 's', 4, 's', 4, 's', 4, 's', '', '2025-07-17 11:37:55'),
(7, NULL, '', '2025-07-17', '18:38:01', 4, 's', 4, 's', 4, 's', 4, 's', '', '2025-07-17 11:38:01'),
(8, NULL, '', '2025-07-17', '18:38:04', 4, 's', 4, 's', 4, 's', 4, 's', '', '2025-07-17 11:38:04'),
(9, NULL, '', '2025-07-17', '19:46:13', 3, 'dsds', 3, 'dsds', 3, 'dsds', 2, 'dsds', 'sdsd', '2025-07-17 12:46:13'),
(10, NULL, 'Tester Otomatis', '2025-07-17', '14:50:48', 5, 'Tes komentar pelayanan.', 4, 'Tes komentar fasilitas.', 5, 'Tes komentar keramahan.', 4, 'Tes komentar kecepatan.', 'Tidak ada saran umum.', '2025-07-17 12:50:48'),
(11, NULL, 'Tester Otomatis', '2025-07-17', '14:51:01', 5, 'Tes komentar pelayanan.', 4, 'Tes komentar fasilitas.', 5, 'Tes komentar keramahan.', 4, 'Tes komentar kecepatan.', 'Tidak ada saran umum.', '2025-07-17 12:51:01'),
(12, NULL, '', '2025-07-17', '19:52:39', 3, 'dsds', 3, 'dsds', 3, 'dsds', 2, 'dsds', 'sdsd', '2025-07-17 12:52:39'),
(13, NULL, '', '2026-01-18', '15:10:54', 5, '', 5, '', 5, '', 5, '', '', '2026-01-18 08:10:54');

-- --------------------------------------------------------

--
-- Table structure for table `tb_profile`
--

CREATE TABLE `tb_profile` (
  `id_profile` int(11) NOT NULL,
  `nama_perusahaan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi_singkat` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visi` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `misi` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_profile`
--

INSERT INTO `tb_profile` (`id_profile`, `nama_perusahaan`, `alamat`, `telepon`, `email`, `website`, `foto`, `foto2`, `deskripsi_singkat`, `visi`, `misi`, `created_at`, `updated_at`) VALUES
(1, 'Kementerian Haji dan Umroh Kabupaten Karawang', 'Jl. Husni Hamid No. 1, Kel. Karawang Wetan Kec. Karawang Barat, Nagasari, Kec. Karawang Bar., Karawang, Jawa Barat 41312', '', '', '', 'logo_696779ab9c2d3.png', 'illust_696779ab9ef4f.png', 'Selamat datang di buku tamu digital kami. Silakan isi data kunjungan Anda.', 'Mewujudkan penyelenggaraan ibadah haji yang profesional, transparan, akuntabel, dan memberikan kepuasan bagi jemaah haji Indonesia.', '1. Pelayanan Prima, Memberikan pelayanan terbaik kepada jemaah haji sejak pendaftaran hingga kepulangan ke tanah air dengan menerapkan standar pelayanan internasional.\r\n2.Transparansi & Akuntabilitas, Mengelola dana dan pelaksanaan ibadah haji secara transparan dan akuntabel dengan sistem pelaporan yang terbuka untuk publik.\r\n3. Inovasi Berkelanjutan, Terus berinovasi dalam sistem penyelenggaraan haji dengan memanfaatkan teknologi informasi untuk efisiensi dan kemudahan jemaah.\r\n4. Kerjasama Strategis, Membangun kerjasama yang solid dengan pemerintah Arab Saudi dan stakeholder lainnya untuk meningkatkan kualitas penyelenggaraan haji.\r\n5. Pembinaan Spiritual, Memberikan pembinaan spiritual dan pembekalan kepada calon jemaah haji agar pelaksanaan ibadah sesuai dengan tuntunan syariat.\r\n6. Kesehatan & Keselamatan, Menjamin kesehatan dan keselamatan jemaah haji dengan standar layanan kesehatan yang memadai di tanah air maupun di Arab Saudi.', '2025-05-09 07:02:32', '2026-01-14 11:10:35');

-- --------------------------------------------------------

--
-- Table structure for table `tb_tamu`
--

CREATE TABLE `tb_tamu` (
  `id_tamu` int(11) NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `waktu_masuk` time DEFAULT NULL,
  `nama_tamu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `asal_instansi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_tamu` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bertemu_dengan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keperluan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan_tambahan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_tamu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanda_tangan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_keluar` enum('Masuk','Keluar') COLLATE utf8mb4_unicode_ci DEFAULT 'Masuk',
  `waktu_keluar` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_tamu`
--

INSERT INTO `tb_tamu` (`id_tamu`, `tanggal_kunjungan`, `waktu_masuk`, `nama_tamu`, `asal_instansi`, `jabatan`, `no_telepon`, `email_tamu`, `bertemu_dengan`, `keperluan`, `catatan_tambahan`, `foto_tamu`, `tanda_tangan`, `status_keluar`, `waktu_keluar`, `created_at`, `updated_at`) VALUES
(27, '2026-01-09', '19:25:39', 'gege', 'karawang', NULL, '0895332357366', NULL, 'manager', 'mendiskusikan kerjasama', NULL, 'tamu_20260109_192539_d614989e.png', NULL, 'Keluar', '08:25:45', '2026-01-09 12:25:39', '2026-01-18 07:25:45'),
(28, '2026-01-14', '17:35:00', 'Airinnisya Addnan', 'Karawang', NULL, '089677452272', NULL, 'Kepala Bagian', 'Ingin daftar haji VVIV', NULL, 'tamu_20260114_173500_97fc24b0.png', NULL, 'Keluar', '08:25:38', '2026-01-14 10:35:00', '2026-01-18 07:25:38'),
(29, '2026-01-14', '17:36:13', 'Salsabillah', 'k', NULL, '', NULL, 'manager', 'mau naik haji', NULL, 'tamu_20260114_173613_71804153.png', NULL, 'Keluar', '08:25:32', '2026-01-14 10:36:13', '2026-01-18 07:25:32'),
(30, '2026-01-14', '18:22:24', 'usman', 'papua', NULL, '081299076849', NULL, 'tara abizair', 'ngopi aja', NULL, 'tamu_20260114_182224_01119488.png', NULL, 'Keluar', '08:25:24', '2026-01-14 11:22:24', '2026-01-18 07:25:24'),
(31, '2026-01-18', '14:18:46', 'Tara', 'Karawang', NULL, '081290822568', NULL, 'Staff', 'Pendaftaran Haji', NULL, 'tamu_20260118_141846_3d1446d1.png', NULL, 'Keluar', '08:24:41', '2026-01-18 07:18:46', '2026-01-18 07:24:41'),
(32, '2026-01-18', '14:37:18', 'Siva', 'Jakarta', NULL, '0771234221133', NULL, 'JFU', 'Pembatalan Haji', NULL, 'tamu_20260118_143718_022a03fa.png', NULL, 'Keluar', '08:38:01', '2026-01-18 07:37:18', '2026-01-18 07:38:01'),
(33, '2026-01-18', '14:40:38', 'Siva', 'Jakarta', NULL, '0987654321', NULL, 'JFU', 'Pembatalan Haji', NULL, 'tamu_20260118_144038_aee289e2.png', NULL, 'Keluar', '14:40:54', '2026-01-18 07:40:38', '2026-01-18 07:40:54'),
(34, '2026-01-18', '14:51:51', 'Tara', 'Good', NULL, '081290822568', NULL, 'Staff', 'Pendaftaran Haji', NULL, 'tamu_20260118_145151_a7b3e1e2.png', NULL, 'Keluar', '15:04:00', '2026-01-18 07:51:51', '2026-01-18 08:04:00'),
(35, '2026-01-18', '15:00:35', 'Tara', 'jakarta', NULL, '0876767672617', NULL, 'Staff', 'Pendaftaran Haji', NULL, 'tamu_20260118_150035_9a6d6247.png', NULL, 'Keluar', '15:04:04', '2026-01-18 08:00:35', '2026-01-18 08:04:04'),
(36, '2026-01-18', '15:04:59', 'Siva', 'Jakarta', NULL, '00112233445566', NULL, 'JFU', 'Pembatalan', NULL, 'tamu_20260118_150459_35df7c04.png', NULL, 'Keluar', '15:09:51', '2026-01-18 08:04:59', '2026-01-18 08:09:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tb_kepuasan`
--
ALTER TABLE `tb_kepuasan`
  ADD PRIMARY KEY (`id_kepuasan`),
  ADD KEY `fk_tamu_kepuasan` (`id_tamu_fk`);

--
-- Indexes for table `tb_profile`
--
ALTER TABLE `tb_profile`
  ADD PRIMARY KEY (`id_profile`);

--
-- Indexes for table `tb_tamu`
--
ALTER TABLE `tb_tamu`
  ADD PRIMARY KEY (`id_tamu`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tb_kepuasan`
--
ALTER TABLE `tb_kepuasan`
  MODIFY `id_kepuasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tb_profile`
--
ALTER TABLE `tb_profile`
  MODIFY `id_profile` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_tamu`
--
ALTER TABLE `tb_tamu`
  MODIFY `id_tamu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_kepuasan`
--
ALTER TABLE `tb_kepuasan`
  ADD CONSTRAINT `fk_tamu_kepuasan` FOREIGN KEY (`id_tamu_fk`) REFERENCES `tb_tamu` (`id_tamu`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
