<?php
// File: isi.php
// (session_start() sudah ada di index.php)

// Pastikan variabel $koneksi sudah ada dari index.php
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    $koneksiPath = __DIR__ . DIRECTORY_SEPARATOR . "koneksi" . DIRECTORY_SEPARATOR . "koneksi.php";
    if (file_exists($koneksiPath)) {
        require_once $koneksiPath;
    } else {
        $_SESSION['gagal'] = "Koneksi database tidak tersedia untuk memproses formulir.";
    }
}

// === AWAL BLOK LOGIKA FORM REGISTRASI TAMU (SUDAH ADA DARI LANGKAH SEBELUMNYA) ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nama_tamu'])) { // Tambahkan isset($_POST['nama_tamu']) untuk membedakan form
    // ... (kode proses form registrasi tamu yang sudah Anda buat sebelumnya) ...
    // Pastikan Anda memiliki cara untuk membedakan submit form ini
    // dari form kepuasan, misal dengan mengecek keberadaan field unik
    // seperti 'nama_tamu' atau menambahkan input hidden dengan nama form.
    // Untuk sekarang, kita anggap jika ada 'nama_tamu' di POST, itu adalah form registrasi.

    // Ambil dan sanitasi data dari form
    $nama_tamu = htmlspecialchars(trim($_POST['nama_tamu'] ?? ''));
    // ... (sisa field registrasi) ...
    $asal_instansi = htmlspecialchars(trim($_POST['asal_instansi'] ?? ''));
    $no_telepon = htmlspecialchars(trim($_POST['no_telepon'] ?? ''));
    $bertemu_dengan = htmlspecialchars(trim($_POST['bertemu_dengan'] ?? ''));
    $keperluan = htmlspecialchars(trim($_POST['keperluan'] ?? ''));

    $foto_tamu_filename = null;
    $foto_processing_error = false;
    if (!empty($_POST['foto_tamu_data'])) {
        $raw_foto_input = $_POST['foto_tamu_data'];
        if (preg_match('/^data:image\/(\w+);base64,/', $raw_foto_input, $match)) {
            $mime_extension = strtolower($match[1]);
            $mime_extension = $mime_extension === 'jpeg' ? 'jpg' : $mime_extension;
            $allowed_extensions = ['jpg', 'jpeg', 'png'];
            if (!in_array($mime_extension, $allowed_extensions, true)) {
                $_SESSION['gagal'] = "Format foto tidak didukung. Gunakan JPG atau PNG.";
                $foto_processing_error = true;
            } else {
                $base64_data = substr($raw_foto_input, strpos($raw_foto_input, ',') + 1);
                $base64_data = str_replace(' ', '+', $base64_data);
                $image_binary = base64_decode($base64_data, true);
                if ($image_binary === false) {
                    $_SESSION['gagal'] = "Foto tidak dapat diproses. Silakan coba lagi.";
                    $foto_processing_error = true;
                } else {
                    $upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tamu';
                    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true)) {
                        $_SESSION['gagal'] = "Folder penyimpanan foto tidak dapat dibuat.";
                        $foto_processing_error = true;
                    } else {
                        try {
                            $random_suffix = bin2hex(random_bytes(4));
                        } catch (\Exception $e) {
                            if (function_exists('openssl_random_pseudo_bytes')) {
                                $random_suffix = bin2hex(openssl_random_pseudo_bytes(4));
                            } else {
                                $random_suffix = sprintf('%08x', mt_rand());
                            }
                        }
                        $foto_tamu_filename = 'tamu_' . date('Ymd_His') . '_' . $random_suffix . '.' . $mime_extension;
                        $foto_path = $upload_dir . DIRECTORY_SEPARATOR . $foto_tamu_filename;
                        if (file_put_contents($foto_path, $image_binary) === false) {
                            $_SESSION['gagal'] = "Foto gagal disimpan. Silakan coba lagi.";
                            $foto_processing_error = true;
                            $foto_tamu_filename = null;
                        }
                    }
                }
            }
        } elseif (trim($raw_foto_input) !== '') {
            $_SESSION['gagal'] = "Foto tidak dikenali. Silakan ambil ulang.";
            $foto_processing_error = true;
        }
    }

    $tanggal_kunjungan = date("Y-m-d");
    $waktu_masuk = date("H:i:s");

    if (empty($nama_tamu) || empty($bertemu_dengan) || empty($keperluan)) {
        $_SESSION['gagal'] = "Kolom Nama Tamu, Bertemu Dengan, dan Keperluan wajib diisi.";
    } elseif ($foto_processing_error) {
    } else {
        // --- CEK DUPLIKASI: Mencegah registrasi ulang jika belum checkout di hari yang sama ---
        $is_duplicate = false;
        // Update: Cek kombinasi Nama DAN No. Telepon.
        // Ini memungkinkan orang berbeda (nama beda) pakai no telepon sama (misal perwakilan),
        // tapi mencegah orang yang SAMA (nama & no telp sama) input double.
        $sql_check = "SELECT id_tamu FROM tb_tamu WHERE nama_tamu = ? AND no_telepon = ? AND tanggal_kunjungan = ? AND (waktu_keluar IS NULL OR waktu_keluar = '') LIMIT 1";
        if ($stmt_check = $koneksi->prepare($sql_check)) {
            $stmt_check->bind_param("sss", $nama_tamu, $no_telepon, $tanggal_kunjungan);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $is_duplicate = true;
                $_SESSION['gagal'] = "Anda masih tercatat di registrasi. Silakan lakukan pertemuan dengan petugas terlebih dahulu.";
            }
            $stmt_check->close();
        }

        if (!$is_duplicate) {
            $sql_tamu = "INSERT INTO tb_tamu (tanggal_kunjungan, waktu_masuk, nama_tamu, asal_instansi, no_telepon, bertemu_dengan, keperluan, foto_tamu) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt_tamu = $koneksi->prepare($sql_tamu)) {
            $stmt_tamu->bind_param("ssssssss",
                $tanggal_kunjungan,
                $waktu_masuk,
                $nama_tamu,
                $asal_instansi,
                $no_telepon,
                $bertemu_dengan,
                $keperluan,
                $foto_tamu_filename
            );
            if ($stmt_tamu->execute()) {
                $_SESSION['sukses'] = "Registrasi kunjungan berhasil disimpan. Terima kasih!";
            } else {
                $_SESSION['gagal'] = "Gagal menyimpan data kunjungan: " . $stmt_tamu->error;
                error_log("Error insert tb_tamu: " . $stmt_tamu->error);
            }
            $stmt_tamu->close();
        } else {
            $_SESSION['gagal'] = "Gagal menyiapkan statement SQL tamu: " . $koneksi->error;
            error_log("Error prepare statement tb_tamu: " . $koneksi->error);
        }
    }
    }
}
// === AKHIR BLOK LOGIKA FORM REGISTRASI TAMU ===


// === AWAL BLOK LOGIKA FORM KEPUASAN ===
// Kita bedakan dengan mengecek salah satu field unik dari form kepuasan, misal 'nilai_pelayanan'
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_kepuasan'])) { // Menggunakan input hidden 'submit_kepuasan'
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        $nama_responden = htmlspecialchars(trim($_POST['nama_responden'] ?? '')); // Bisa diisi atau tidak
        // Ambil id_tamu terakhir jika ada (misalnya, jika survei diisi setelah registrasi)
        // Ini contoh sederhana, implementasi sebenarnya mungkin lebih kompleks
        // $id_tamu_fk = isset($_SESSION['last_guest_id']) ? $_SESSION['last_guest_id'] : null;
        // Untuk sekarang, kita biarkan id_tamu_fk tidak diisi otomatis dari session, bisa diisi manual jika ada fieldnya
        $id_tamu_fk_input = filter_input(INPUT_POST, 'id_tamu_fk', FILTER_VALIDATE_INT);
        $id_tamu_fk = $id_tamu_fk_input ?: null;


        $nilai_pelayanan = filter_input(INPUT_POST, 'nilai_pelayanan', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
        $nilai_fasilitas = filter_input(INPUT_POST, 'nilai_fasilitas', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
        $nilai_keramahan = filter_input(INPUT_POST, 'nilai_keramahan', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
        $nilai_kecepatan = filter_input(INPUT_POST, 'nilai_kecepatan', FILTER_VALIDATE_INT, ["options" => ["min_range"=>1, "max_range"=>5]]);
        $saran_masukan = htmlspecialchars(trim($_POST['saran_masukan'] ?? ''));

        $tanggal_survei = date("Y-m-d");
        $waktu_survei = date("H:i:s");

        // Validasi
        if ($nilai_pelayanan === false || $nilai_fasilitas === false || $nilai_keramahan === false || $nilai_kecepatan === false) {
            $_SESSION['gagal'] = "Semua pertanyaan penilaian wajib diisi dengan benar (skala 1-5).";
        } else {
            $sql_kepuasan = "INSERT INTO tb_kepuasan (id_tamu_fk, nama_responden, tanggal_survei, waktu_survei, nilai_pelayanan, nilai_fasilitas, nilai_keramahan, nilai_kecepatan, saran_masukan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt_kepuasan = $koneksi->prepare($sql_kepuasan)) {
                // i untuk integer, s untuk string
                $stmt_kepuasan->bind_param("isssiiiis",
                    $id_tamu_fk, // Jika tidak ada, akan jadi NULL
                    $nama_responden,
                    $tanggal_survei,
                    $waktu_survei,
                    $nilai_pelayanan,
                    $nilai_fasilitas,
                    $nilai_keramahan,
                    $nilai_kecepatan,
                    $saran_masukan
                );

                if ($stmt_kepuasan->execute()) {
                    $_SESSION['sukses'] = "Survei kepuasan Anda berhasil dikirim. Terima kasih atas partisipasinya!";
                    // header("Location: index.php?page=spk"); // Redirect untuk clear form
                    // exit();
                } else {
                    $_SESSION['gagal'] = "Gagal menyimpan data survei: " . $stmt_kepuasan->error;
                    error_log("Error insert tb_kepuasan: " . $stmt_kepuasan->error);
                }
                $stmt_kepuasan->close();
            } else {
                $_SESSION['gagal'] = "Gagal menyiapkan statement SQL survei: " . $koneksi->error;
                error_log("Error prepare statement tb_kepuasan: " . $koneksi->error);
            }
        }
    } else {
        $_SESSION['gagal'] = "Koneksi database tidak tersedia. Tidak dapat menyimpan data survei.";
    }
    // Redirect setelah POST untuk mencegah resubmit dan membersihkan URL
    // header("Location: index.php?page=spk");
    // exit;
}
// === AKHIR BLOK LOGIKA FORM KEPUASAN ===


// === AWAL BLOK TAMPILAN KONTEN (HTML) ===
// Logika untuk Form Indeks Kepuasan (akan kita isi nanti)
if (isset($_GET['page']) && $_GET['page'] === 'spk') {
?>
    <form id="formKepuasan" method="POST" action="index.php?page=spk">
        <input type="hidden" name="submit_kepuasan" value="1"> <div class="mb-3">
            <label for="nama_responden" class="form-label"><i class="bi bi-person-check-fill"></i> Nama Anda (Opsional)</label>
            <input type="text" class="form-control" id="nama_responden" name="nama_responden" placeholder="Boleh dikosongkan jika ingin anonim">
        </div>

        <p class="fw-bold mb-1">Berikan penilaian Anda (1 = Sangat Buruk, 5 = Sangat Baik):</p>

        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <label class="form-label d-block"><i class="bi bi-headset"></i> Kualitas Pelayanan:</label>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="nilai_pelayanan" id="pelayanan_<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                    <label class="form-check-label" for="pelayanan_<?php echo $i; ?>"><?php echo $i; ?></label>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <label class="form-label d-block"><i class="bi bi-building-gear"></i> Fasilitas yang Tersedia:</label>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="nilai_fasilitas" id="fasilitas_<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                    <label class="form-check-label" for="fasilitas_<?php echo $i; ?>"><?php echo $i; ?></label>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <label class="form-label d-block"><i class="bi bi-emoji-smile-fill"></i> Keramahan Staf:</label>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="nilai_keramahan" id="keramahan_<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                    <label class="form-check-label" for="keramahan_<?php echo $i; ?>"><?php echo $i; ?></label>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <label class="form-label d-block"><i class="bi bi-clock-history"></i> Kecepatan Layanan:</label>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="nilai_kecepatan" id="kecepatan_<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                    <label class="form-check-label" for="kecepatan_<?php echo $i; ?>"><?php echo $i; ?></label>
                </div>
                <?php endfor; ?>
            </div>
        </div>


        <div class="mb-4">
            <label for="saran_masukan" class="form-label"><i class="bi bi-chat-quote-fill"></i> Saran dan Masukan (Opsional):</label>
            <textarea class="form-control" id="saran_masukan" name="saran_masukan" rows="3" placeholder="Sampaikan saran atau masukan Anda untuk perbaikan layanan kami"></textarea>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-submit-modern">
                <i class="bi bi-send-check-fill me-2"></i>Kirim Survei
            </button>
        </div>
        <p class="mt-3 text-center">
            <a href="index.php" class="text-decoration-none"><i class="bi bi-arrow-left-circle"></i> Kembali ke Registrasi Tamu</a>
        </p>
    </form>

<?php
} else {
// Tampilkan Form Registrasi Tamu jika bukan halaman SPK (KODE INI SUDAH ADA DARI LANGKAH SEBELUMNYA)
?>
    <form id="formRegistrasiTamu" method="POST" action="index.php">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="nama_tamu" class="form-label"><i class="bi bi-person-fill"></i> Nama Lengkap Tamu <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_tamu" name="nama_tamu" placeholder="Masukkan nama lengkap Anda" required>
            </div>
            <div class="col-md-6">
                <label for="asal_instansi" class="form-label"><i class="bi bi-building"></i> Asal Tamu <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="asal_instansi" name="asal_instansi" placeholder="Contoh: Instansi, Kota, atau Pribadi" required>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <label for="no_telepon" class="form-label"><i class="bi bi-telephone-fill"></i> Nomor Telepon <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="no_telepon" name="no_telepon" placeholder="Contoh: 081234567890" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="bertemu_dengan" class="form-label"><i class="bi bi-people-fill"></i> Bertemu Dengan Siapa/Bagian Apa <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="bertemu_dengan" name="bertemu_dengan" placeholder="Nama orang atau bagian yang dituju" required>
        </div>

        <div class="mb-4">
            <label for="keperluan" class="form-label"><i class="bi bi-chat-left-dots-fill"></i> Keperluan Kunjungan <span class="text-danger">*</span></label>
            <textarea class="form-control" id="keperluan" name="keperluan" rows="3" placeholder="Jelaskan tujuan kunjungan Anda" required></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label d-block"><i class="bi bi-camera-video-fill"></i> Ambil Foto Tamu <span class="text-danger">*</span></label>
            <div class="camera-wrapper border rounded p-3 bg-light">
                <div class="camera-preview position-relative overflow-hidden rounded mb-3">
                    <video id="kameraTamuPreview" class="w-100 d-none rounded" autoplay playsinline></video>
                    <img id="kameraTamuSnapshot" class="img-fluid rounded d-none" alt="Hasil foto tamu">
                    <div id="kameraPlaceholder" class="ratio ratio-4x3 bg-white border rounded" style="border-style: dashed !important; border-width: 2px;">
                        <div class="d-flex w-100 h-100 align-items-center justify-content-center flex-column text-muted p-3 text-center">
                            <i class="bi bi-camera-fill mb-2" style="font-size: 3rem; opacity: 0.5;"></i>
                            <span class="fw-medium">Nyalakan kamera untuk mengambil foto</span>
                            <small class="d-block mt-1 opacity-75">Klik "Mulai Kamera" di bawah</small>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary" id="btnMulaiKamera"><i class="bi bi-camera-video"></i> Mulai Kamera</button>
                    <button type="button" class="btn btn-primary" id="btnAmbilFoto" disabled><i class="bi bi-camera"></i> Ambil Foto</button>
                    <button type="button" class="btn btn-outline-secondary" id="btnUlangFoto" disabled><i class="bi bi-arrow-counterclockwise"></i> Ulangi</button>
                </div>
                <p class="small text-muted mt-2 mb-0" id="kameraStatus">Pastikan perangkat memiliki kamera dan izinkan akses saat diminta.</p>
            </div>
            <input type="hidden" name="foto_tamu_data" id="fotoTamuData" required>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-submit-modern">
                <i class="bi bi-check-circle-fill me-2"></i>Kirim Registrasi
            </button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formRegistrasiTamu');
            if (!form) {
                return;
            }

            const startBtn = document.getElementById('btnMulaiKamera');
            const captureBtn = document.getElementById('btnAmbilFoto');
            const retakeBtn = document.getElementById('btnUlangFoto');
            const videoEl = document.getElementById('kameraTamuPreview');
            const snapshotImg = document.getElementById('kameraTamuSnapshot');
            const placeholder = document.getElementById('kameraPlaceholder');
            const statusEl = document.getElementById('kameraStatus');
            const hiddenInput = document.getElementById('fotoTamuData');

            let streamHandle = null;

            function updateStatus(message, type = 'muted') {
                if (statusEl) {
                    statusEl.textContent = message;
                    statusEl.classList.remove('text-success', 'text-danger', 'text-muted', 'text-warning');
                    switch (type) {
                        case 'success':
                            statusEl.classList.add('text-success');
                            break;
                        case 'danger':
                            statusEl.classList.add('text-danger');
                            break;
                        case 'warning':
                            statusEl.classList.add('text-warning');
                            break;
                        default:
                            statusEl.classList.add('text-muted');
                    }
                }
            }

            function stopStream() {
                if (streamHandle) {
                    const tracks = streamHandle.getTracks();
                    tracks.forEach(track => track.stop());
                    streamHandle = null;
                }
                if (videoEl) {
                    videoEl.classList.add('d-none');
                    if (typeof videoEl.pause === 'function') {
                        videoEl.pause();
                    }
                    if ('srcObject' in videoEl) {
                        videoEl.srcObject = null;
                    } else {
                        videoEl.removeAttribute('src');
                    }
                }
                if (placeholder) {
                    placeholder.classList.remove('d-none');
                }
                if (captureBtn) {
                    captureBtn.disabled = true;
                }
                if (retakeBtn) {
                    retakeBtn.disabled = true;
                }
            }

            function showSnapshot(dataUrl) {
                if (snapshotImg) {
                    snapshotImg.src = dataUrl;
                    snapshotImg.classList.remove('d-none');
                }
                if (videoEl) {
                    videoEl.classList.add('d-none');
                }
                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
                if (retakeBtn) {
                    retakeBtn.disabled = false;
                }
                if (captureBtn) {
                    captureBtn.disabled = false;
                }
                if (startBtn) {
                    startBtn.disabled = false;
                }
            }

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (startBtn) {
                    startBtn.disabled = true;
                }
                if (captureBtn) {
                    captureBtn.disabled = true;
                }
                if (retakeBtn) {
                    retakeBtn.disabled = true;
                }
                updateStatus('Browser tidak mendukung akses kamera. Anda bisa melanjutkan tanpa foto.', 'warning');
                return;
            }

            if (startBtn) {
                startBtn.addEventListener('click', async () => {
                    try {
                        if (!streamHandle) {
                            streamHandle = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                        }
                        if (videoEl) {
                            videoEl.srcObject = streamHandle;
                            await videoEl.play();
                            videoEl.classList.remove('d-none');
                        }
                        if (snapshotImg) {
                            snapshotImg.classList.add('d-none');
                        }
                        if (placeholder) {
                            placeholder.classList.add('d-none');
                        }
                        if (captureBtn) {
                            captureBtn.disabled = false;
                        }
                        if (retakeBtn) {
                            retakeBtn.disabled = true;
                        }
                        updateStatus('Kamera aktif. Silakan posisikan wajah tamu lalu tekan Ambil Foto.', 'success');
                    } catch (error) {
                        const message = (error && error.message) ? error.message : 'Periksa izin kamera.';
                        updateStatus('Gagal mengakses kamera: ' + message, 'danger');
                        if (captureBtn) {
                            captureBtn.disabled = true;
                        }
                        if (retakeBtn) {
                            retakeBtn.disabled = true;
                        }
                        stopStream();
                    }
                });
            }

            if (captureBtn) {
                captureBtn.addEventListener('click', () => {
                    if (!videoEl || videoEl.readyState < 2) {
                        updateStatus('Kamera belum siap. Silakan coba kembali.', 'warning');
                        return;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = videoEl.videoWidth;
                    canvas.height = videoEl.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
                    const dataUrl = canvas.toDataURL('image/png', 0.92);
                    if (hiddenInput) {
                        hiddenInput.value = dataUrl;
                    }
                    showSnapshot(dataUrl);
                    updateStatus('Foto berhasil diambil. Jika diperlukan, klik Ulangi.', 'success');
                });
            }

            if (retakeBtn) {
                retakeBtn.addEventListener('click', () => {
                    if (!streamHandle) {
                        if (hiddenInput) {
                            hiddenInput.value = '';
                        }
                        if (snapshotImg) {
                            snapshotImg.classList.add('d-none');
                        }
                        if (placeholder) {
                            placeholder.classList.remove('d-none');
                        }
                        if (captureBtn) {
                            captureBtn.disabled = true;
                        }
                        if (retakeBtn) {
                            retakeBtn.disabled = true;
                        }
                        updateStatus('Kamera belum dinyalakan. Tekan Mulai Kamera untuk mencoba lagi.', 'muted');
                        return;
                    }
                    if (videoEl) {
                        videoEl.classList.remove('d-none');
                        videoEl.play();
                    }
                    if (snapshotImg) {
                        snapshotImg.classList.add('d-none');
                        snapshotImg.removeAttribute('src');
                    }
                    if (placeholder) {
                        placeholder.classList.add('d-none');
                    }
                    if (hiddenInput) {
                        hiddenInput.value = '';
                    }
                    if (captureBtn) {
                        captureBtn.disabled = false;
                    }
                    updateStatus('Silakan ambil foto baru.', 'success');
                });
            }

            form.addEventListener('submit', () => {
                if (hiddenInput && !hiddenInput.value) {
                    updateStatus('Foto belum diambil. Anda tetap bisa melanjutkan tanpa foto.', 'warning');
                }
                stopStream();
            });

            window.addEventListener('beforeunload', stopStream);
        });
    </script>
<?php
} // Akhir dari else (untuk tampilan form registrasi)
// === AKHIR BLOK TAMPILAN KONTEN (HTML) ===
?>