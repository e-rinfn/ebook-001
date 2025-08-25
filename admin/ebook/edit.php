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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $deskripsi = $_POST['deskripsi'];
    $kategori_ids = $_POST['kategori_ids'] ?? [];

    $cover_url = $ebook['cover_url'];
    if ($_FILES['cover']['size'] > 0) {
        // Hapus cover lama jika ada
        if ($cover_url && file_exists("../../uploads/covers/$cover_url")) {
            unlink("../../uploads/covers/$cover_url");
        }
        $cover_url = uploadFile($_FILES['cover'], '../../uploads/covers/');
    }

    $file_url = $ebook['file_url'];
    if ($_FILES['file']['size'] > 0) {
        // Hapus file lama jika ada
        if ($file_url && file_exists("../../uploads/ebooks/$file_url")) {
            unlink("../../uploads/ebooks/$file_url");
        }
        $file_url = uploadFile($_FILES['file'], '../../uploads/ebooks/');
    }

    // Update ebook
    $stmt = $pdo->prepare("UPDATE ebook SET 
                          judul = ?, penulis = ?, tahun_terbit = ?, deskripsi = ?, 
                          cover_url = ?, file_url = ?, updated_at = NOW() 
                          WHERE id = ?");
    $stmt->execute([$judul, $penulis, $tahun_terbit, $deskripsi, $cover_url, $file_url, $id]);

    // Update kategori
    // Hapus semua kategori lama
    $pdo->prepare("DELETE FROM ebook_kategori WHERE ebook_id = ?")->execute([$id]);

    // Tambahkan kategori baru
    foreach ($kategori_ids as $kategori_id) {
        $stmt = $pdo->prepare("INSERT INTO ebook_kategori (ebook_id, kategori_id) VALUES (?, ?)");
        $stmt->execute([$id, $kategori_id]);
    }

    redirect('index.php?success=edit');
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
                                <div class="col-md-6">
                                    <label class="form-label">Judul</label>
                                    <input type="text" name="judul" class="form-control form-control-sm" value="<?= htmlspecialchars($ebook['judul']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Penulis</label>
                                    <input type="text" name="penulis" class="form-control form-control-sm" value="<?= htmlspecialchars($ebook['penulis']) ?>" required>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label">Tahun Terbit</label>
                                    <input type="number" name="tahun_terbit" class="form-control form-control-sm" min="1900" max="<?= date('Y') ?>" value="<?= $ebook['tahun_terbit'] ?>" required>
                                </div>
                                <div class="col-md-8 mt-3">
                                    <label class="form-label">Kategori</label><br>
                                    <?php foreach ($kategoris as $kategori): ?>
                                        <div class="form-check form-check-inline mb-1">
                                            <input class="form-check-input" type="checkbox" name="kategori_ids[]" id="kat<?= $kategori['id'] ?>" value="<?= $kategori['id'] ?>" <?= in_array($kategori['id'], $selectedKategoris) ? 'checked' : '' ?>>
                                            <label class="form-check-label small" for="kat<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['nama']) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($ebook['deskripsi']) ?></textarea>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label class="form-label d-block">Cover Saat Ini</label>
                                    <?php if ($ebook['cover_url']): ?>
                                        <img src="../../uploads/covers/<?= $ebook['cover_url'] ?>" class="img-thumbnail mb-2" style="max-width: 100px;">
                                    <?php else: ?>
                                        <p class="text-muted small">Tidak ada cover</p>
                                    <?php endif; ?>
                                    <input type="file" name="cover" class="form-control form-control-sm mt-1" accept="image/*">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label class="form-label d-block">File Saat Ini</label>
                                    <p class="form-text small mb-1"><?= $ebook['file_url'] ?></p>
                                    <input type="file" name="file" class="form-control form-control-sm" accept=".pdf">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                                <a href="index.php" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-x-circle me-1"></i> Batal
                                </a>
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