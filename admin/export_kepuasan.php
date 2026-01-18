<?php
session_start();
require_once __DIR__ . '/../koneksi/koneksi.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit("Akses ditolak. Silakan login terlebih dahulu.");
}

// Nama file Excel
$filename = "daftar_survei_kepuasan_" . date('Ymd_His') . ".xls";

// Header untuk Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Query Data
$sql_export_survei = "SELECT 
                        ks.id_kepuasan, 
                        ks.id_tamu_fk, 
                        t.nama_tamu AS nama_tamu_terkait,
                        ks.nama_responden,
                        ks.tanggal_survei, 
                        ks.waktu_survei, 
                        ks.nilai_pelayanan, 
                        ks.nilai_fasilitas, 
                        ks.nilai_keramahan, 
                        ks.nilai_kecepatan, 
                        ks.saran_masukan,
                        ks.created_at
                    FROM tb_kepuasan ks
                    LEFT JOIN tb_tamu t ON ks.id_tamu_fk = t.id_tamu
                    ORDER BY ks.tanggal_survei DESC, ks.waktu_survei DESC";

$result_export = $koneksi->query($sql_export_survei);

if ($result_export === false) {
    error_log("Gagal query ekspor data survei kepuasan: " . $koneksi->error);
    exit("Terjadi kesalahan saat mengambil data survei.");
}

// Styling HTML untuk Excel
echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><style>
    body { font-family: Arial, sans-serif; }
    .export-title { text-align: center; font-size: 20px; font-weight: bold; color: #5D4037; padding: 10px 0; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #b7c9e2; padding: 5px; vertical-align: middle; }
    th { background-color: #5D4037; color: #ffffff; font-weight: bold; text-align: center; }
    tr:nth-child(even) td { background-color: #f8f9fa; }
    .text-center { text-align: center; }
</style></head><body>';

echo '<div class="export-title">Laporan Survei Kepuasan Masyarakat</div>';
echo '<div style="text-align: center; margin-bottom: 10px; color: #555;">Diekspor pada: ' . date('d-m-Y H:i:s') . '</div>';

echo '<table><thead><tr>
        <th>No.</th>
        <th>ID Survei</th>
        <th>Nama Responden</th>
        <th>Tanggal Survei</th>
        <th>Waktu</th>
        <th>Tamu Terkait</th>
        <th>Pelayanan</th>
        <th>Fasilitas</th>
        <th>Keramahan</th>
        <th>Kecepatan</th>
        <th>Rata-rata</th>
        <th>Saran & Masukan</th>
    </tr></thead><tbody>';

$no = 1;
if ($result_export->num_rows > 0) {
    while ($row = $result_export->fetch_assoc()) {
        $avg_score = ($row['nilai_pelayanan'] + $row['nilai_fasilitas'] + $row['nilai_keramahan'] + $row['nilai_kecepatan']) / 4;
        
        echo '<tr>';
        echo '<td class="text-center">' . $no++ . '</td>';
        echo '<td class="text-center">' . htmlspecialchars($row['id_kepuasan']) . '</td>';
        echo '<td>' . htmlspecialchars($row['nama_responden']) . '</td>';
        echo '<td class="text-center">' . htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_survei']))) . '</td>';
        echo '<td class="text-center">' . htmlspecialchars(date('H:i', strtotime($row['waktu_survei']))) . '</td>';
        echo '<td>' . htmlspecialchars($row['nama_tamu_terkait'] ?? '-') . '</td>';
        echo '<td class="text-center">' . $row['nilai_pelayanan'] . '</td>';
        echo '<td class="text-center">' . $row['nilai_fasilitas'] . '</td>';
        echo '<td class="text-center">' . $row['nilai_keramahan'] . '</td>';
        echo '<td class="text-center">' . $row['nilai_kecepatan'] . '</td>';
        echo '<td class="text-center" style="font-weight:bold;">' . number_format($avg_score, 1) . '</td>';
        echo '<td>' . htmlspecialchars($row['saran_masukan']) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="12" class="text-center">Belum ada data survei kepuasan.</td></tr>';
}

echo '</tbody></table></body></html>';

$result_export->free();
if (isset($koneksi) && $koneksi instanceof mysqli) {
    $koneksi->close();
}
exit;
?>