<?php
/**
 * File Koneksi Database
 */
date_default_timezone_set('Asia/Jakarta'); // Set timezone ke Waktu Indonesia Barat

$namaHost = "localhost";
$namaPengguna = "root";
$kataSandi = ""; // Kosongkan jika default XAMPP tanpa password
$namaDatabase = "db_bukutamu_digital"; // Pastikan nama ini benar

$koneksi = new mysqli($namaHost, $namaPengguna, $kataSandi, $namaDatabase);

if ($koneksi->connect_error) {
    error_log("Koneksi ke database gagal: " . $koneksi->connect_error);
    die("Maaf, terjadi masalah saat menghubungkan ke database. Silakan coba lagi nanti.");
}

if (!$koneksi->set_charset("utf8mb4")) {
    error_log("Gagal mengatur set karakter koneksi ke utf8mb4: " . $koneksi->error);
}
?>