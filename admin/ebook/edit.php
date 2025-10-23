<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$id = $_GET['id'] ?? 0;
$ebook = $pdo->prepare("SELECT * FROM ebook WHERE id = ?");
$ebook->execute([$id]);
$ebook = $ebook->fetch(PDO::FETCH_ASSOC);

if (!$ebook) {
    redirect('index.php');
}

// Ambil kategori yang sudah dipilih
$selectedKategoris = $pdo->prepare("SELECT kategori_id FROM ebook_kategori WHERE ebook_id = ?");
$selectedKategoris->execute([$id]);
$selectedKategoris = $selectedKategoris->fetchAll(PDO::FETCH_COLUMN);

$kategoris = $pdo->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC);

// Fungsi untuk mendapatkan nomor urut berikutnya
function getNextSequenceNumber($pdo, $table, $column)
{
    $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX($column, '_', 1), '_', -1) AS UNSIGNED)) as max_number FROM $table WHERE $column REGEXP '^[0-9]+_'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($result['max_number'] ?? 0) + 1;
}

// Fungsi upload file dengan sequence number
function uploadFileWithSequence($file, $uploadPath, $pdo, $table, $column)
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }

    // Generate sequence number
    $sequence = getNextSequenceNumber($pdo, $table, $column);
    $sequenceFormatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);

    // Dapatkan ekstensi file asli
    $originalName = $file['name'];
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);

    // Hapus karakter khusus dari nama file
    $cleanName = preg_replace('/[^a-zA-Z0-9\s\-\.]/', '', pathinfo($originalName, PATHINFO_FILENAME));
    $cleanName = str_replace(' ', '_', $cleanName);

    // Buat nama file baru: 0001_nama_file_asli.ext
    $newFilename = $sequenceFormatted . '_' . $cleanName . '.' . $extension;

    // Pastikan direktori upload ada
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $destination = $uploadPath . $newFilename;

    // Pindahkan file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to move uploaded file.');
    }

    return $newFilename;
}

// Fungsi untuk mengekstrak sequence number dari filename yang sudah ada
function extractSequenceNumber($filename)
{
    if (preg_match('/^(\d+)_/', $filename, $matches)) {
        return (int)$matches[1];
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $deskripsi = $_POST['deskripsi'];
    $kategori_ids = $_POST['kategori_ids'] ?? [];
    $jumlah_halaman = $_POST['jumlah_halaman'] ?? 0;
    $isbn = $_POST['isbn'] ?? '';

    try {
        $cover_url = $ebook['cover_url'];
        if ($_FILES['cover']['size'] > 0) {
            // Hapus cover lama jika ada
            if ($cover_url && file_exists("../../uploads/covers/$cover_url")) {
                unlink("../../uploads/covers/$cover_url");
            }

            // Upload cover baru dengan sequence number
            $cover_url = uploadFileWithSequence($_FILES['cover'], '../../uploads/covers/', $pdo, 'ebook', 'cover_url');
        } else {
            // Jika tidak upload file baru, pertahankan sequence number yang lama
            $oldSequence = extractSequenceNumber($cover_url);
            if ($oldSequence) {
                // Ekstrak nama asli dari judul untuk konsistensi
                $cleanJudul = preg_replace('/[^a-zA-Z0-9\s\-\.]/', '', $judul);
                $cleanJudul = str_replace(' ', '_', $cleanJudul);
                $extension = pathinfo($cover_url, PATHINFO_EXTENSION);

                // Buat nama file baru dengan sequence lama tapi judul baru
                $newCoverName = str_pad($oldSequence, 4, '0', STR_PAD_LEFT) . '_' . $cleanJudul . '.' . $extension;

                // Rename file jika berbeda
                if ($newCoverName !== $cover_url && file_exists("../../uploads/covers/$cover_url")) {
                    rename("../../uploads/covers/$cover_url", "../../uploads/covers/$newCoverName");
                    $cover_url = $newCoverName;
                }
            }
        }

        $file_url = $ebook['file_url'];
        if ($_FILES['file']['size'] > 0) {
            // Hapus file lama jika ada
            if ($file_url && file_exists("../../uploads/ebooks/$file_url")) {
                unlink("../../uploads/ebooks/$file_url");
            }

            // Upload file baru dengan sequence number
            $file_url = uploadFileWithSequence($_FILES['file'], '../../uploads/ebooks/', $pdo, 'ebook', 'file_url');
        } else {
            // Jika tidak upload file baru, pertahankan sequence number yang lama
            $oldSequence = extractSequenceNumber($file_url);
            if ($oldSequence) {
                // Ekstrak nama asli dari judul untuk konsistensi
                $cleanJudul = preg_replace('/[^a-zA-Z0-9\s\-\.]/', '', $judul);
                $cleanJudul = str_replace(' ', '_', $cleanJudul);
                $extension = pathinfo($file_url, PATHINFO_EXTENSION);

                // Buat nama file baru dengan sequence lama tapi judul baru
                $newFileName = str_pad($oldSequence, 4, '0', STR_PAD_LEFT) . '_' . $cleanJudul . '.' . $extension;

                // Rename file jika berbeda
                if ($newFileName !== $file_url && file_exists("../../uploads/ebooks/$file_url")) {
                    rename("../../uploads/ebooks/$file_url", "../../uploads/ebooks/$newFileName");
                    $file_url = $newFileName;
                }
            }
        }

        // Update ebook
        $stmt = $pdo->prepare("UPDATE ebook SET 
                              judul = ?, penulis = ?, tahun_terbit = ?, deskripsi = ?, 
                              cover_url = ?, file_url = ?, jumlah_halaman = ?, isbn = ?, updated_at = NOW() 
                              WHERE id = ?");
        $stmt->execute([$judul, $penulis, $tahun_terbit, $deskripsi, $cover_url, $file_url, $jumlah_halaman, $isbn, $id]);

        // Update kategori
        // Hapus semua kategori lama
        $pdo->prepare("DELETE FROM ebook_kategori WHERE ebook_id = ?")->execute([$id]);

        // Tambahkan kategori baru
        foreach ($kategori_ids as $kategori_id) {
            $stmt = $pdo->prepare("INSERT INTO ebook_kategori (ebook_id, kategori_id) VALUES (?, ?)");
            $stmt->execute([$id, $kategori_id]);
        }

        redirect('index.php?success=edit');
    } catch (Exception $e) {
        // Handle error
        $error = "Error: " . $e->getMessage();
        // Anda bisa menampilkan error atau menyimpannya dalam session
    }
}
?>
<!-- Header -->
<?php include '../../includes/head.php'; ?>
<!-- /Header -->

<body class="">
    <div class="page">
        <div class="page-main">
            <div class="header py-4">

                <!-- Navbar -->
                <?php include '../../includes/navbar.php'; ?>
                <!-- / Navbar -->

                <div class="my-3 my-md-5">
                    <div class="container">
                        <div class="page-header">
                            <h1 class="page-title">
                                EDIT E-BOOK
                            </h1>
                        </div>
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                E-Book berhasil <?= $_GET['success'] === 'add' ? 'ditambahkan' : 'diperbarui' ?>!
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="border p-3 bg-light rounded">
                            <div class="row g-2">
                                <!-- Judul -->
                                <div class="col-md-6">
                                    <label class="form-label">Judul</label>
                                    <input type="text" name="judul" class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($ebook['judul']) ?>" required>
                                </div>

                                <!-- Penulis -->
                                <div class="col-md-6">
                                    <label class="form-label">Penulis</label>
                                    <input type="text" name="penulis" class="form-control form-control-sm"
                                        value="<?= htmlspecialchars($ebook['penulis']) ?>" required>
                                </div>

                                <!-- Tahun Terbit -->
                                <div class="col-md-3 mt-3">
                                    <label class="form-label">Tahun Terbit</label>
                                    <input type="number" name="tahun_terbit" class="form-control form-control-sm"
                                        min="1900" max="<?= date('Y') ?>"
                                        value="<?= htmlspecialchars($ebook['tahun_terbit']) ?>" required>
                                </div>

                                <!-- Jumlah Halaman -->
                                <div class="col-md-3 mt-3">
                                    <label class="form-label">Jumlah Halaman</label>
                                    <input type="number" name="jumlah_halaman" class="form-control form-control-sm"
                                        min="1" placeholder="Masukkan jumlah halaman"
                                        value="<?= htmlspecialchars($ebook['jumlah_halaman'] ?? '') ?>" required>
                                </div>

                                <!-- ISBN -->
                                <div class="col-md-6 mt-3">
                                    <label class="form-label">ISBN</label>
                                    <input type="text" name="isbn" class="form-control form-control-sm"
                                        placeholder="Contoh: 978-602-03-1234-5"
                                        pattern="[0-9\-]+"
                                        title="Hanya angka dan tanda '-' yang diperbolehkan"
                                        value="<?= htmlspecialchars($ebook['isbn'] ?? '') ?>">
                                </div>

                                <!-- Kategori -->
                                <div class="col-md-12 mt-3">
                                    <label class="form-label">Kategori</label><br>
                                    <?php foreach ($kategoris as $kategori): ?>
                                        <div class="form-check form-check-inline mb-1">
                                            <input class="form-check-input" type="checkbox" name="kategori_ids[]"
                                                id="kat<?= $kategori['id'] ?>"
                                                value="<?= $kategori['id'] ?>"
                                                <?= in_array($kategori['id'], $selectedKategoris) ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="kat<?= $kategori['id'] ?>">
                                                <?= htmlspecialchars($kategori['nama']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Deskripsi -->
                                <div class="col-12 mt-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($ebook['deskripsi']) ?></textarea>
                                </div>

                                <!-- Cover -->
                                <div class="col-md-6 mt-3">
                                    <label class="form-label d-block">Cover Saat Ini</label>
                                    <?php if (!empty($ebook['cover_url'])): ?>
                                        <img src="../../uploads/covers/<?= htmlspecialchars($ebook['cover_url']) ?>"
                                            class="img-thumbnail mb-2" style="max-width: 100px;">
                                    <?php else: ?>
                                        <p class="text-muted small">Tidak ada cover</p>
                                    <?php endif; ?>
                                    <input type="file" name="cover" class="form-control form-control-sm mt-1" accept="image/*">
                                </div>

                                <!-- File eBook -->
                                <div class="col-md-6 mt-3">
                                    <label class="form-label d-block">File Saat Ini</label>
                                    <?php if (!empty($ebook['file_url'])): ?>
                                        <p class="form-text small mb-1"><?= htmlspecialchars($ebook['file_url']) ?></p>
                                    <?php else: ?>
                                        <p class="text-muted small">Tidak ada file PDF</p>
                                    <?php endif; ?>
                                    <input type="file" name="file" class="form-control form-control-sm" accept=".pdf">
                                </div>
                            </div>

                            <!-- Tombol -->
                            <div class="d-flex justify-content-between mt-3">
                                <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <footer class="footer">
                <div class="container">
                    <div class="row align-items-center flex-row-reverse">

                        <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-center">
                            Copyright © 2025 <a href=".">E-Book Buku Pelajaran</a>.
                        </div>
                    </div>
                </div>
            </footer>
        </div>

        <style>
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                display: none;
            }

            .loading-spinner {
                width: 50px;
                height: 50px;
                border: 5px solid #f3f3f3;
                border-top: 5px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }
        </style>

        <div class="loading-overlay" id="loadingOverlay">
            <div class="loading-spinner"></div>
        </div>

        <script>
            document.querySelector('form').addEventListener('submit', function(e) {
                // Show loading overlay
                document.getElementById('loadingOverlay').style.display = 'flex';

                // You can optionally disable the submit button to prevent multiple submissions
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
            });

            // In case there's a validation error and the page reloads, make sure the button is reset
            window.addEventListener('load', function() {
                const submitBtn = document.querySelector('form button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Simpan';
                }
            });
        </script>
</body>

</html>