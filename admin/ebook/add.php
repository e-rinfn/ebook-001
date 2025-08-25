<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$kategoris = $pdo->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $deskripsi = $_POST['deskripsi'];
    $kategori_ids = $_POST['kategori_ids'] ?? [];

    // Upload cover
    $cover_url = uploadFile($_FILES['cover'], '../../uploads/covers/');

    // Upload file ebook
    $file_url = uploadFile($_FILES['file'], '../../uploads/ebooks/');

    // Simpan ke database
    $stmt = $pdo->prepare("INSERT INTO ebook 
                          (judul, penulis, tahun_terbit, deskripsi, cover_url, file_url, admin_id) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$judul, $penulis, $tahun_terbit, $deskripsi, $cover_url, $file_url, $_SESSION['admin_id']]);

    $ebook_id = $pdo->lastInsertId();

    // Simpan kategori
    foreach ($kategori_ids as $kategori_id) {
        $stmt = $pdo->prepare("INSERT INTO ebook_kategori (ebook_id, kategori_id) VALUES (?, ?)");
        $stmt->execute([$ebook_id, $kategori_id]);
    }

    redirect('index.php?success=add');
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
                                TAMBAH E-BOOK
                            </h1>
                        </div>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data" class="border p-3 bg-light rounded">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Judul</label>
                                    <input type="text" name="judul" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Penulis</label>
                                    <input type="text" name="penulis" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4 mt-3">
                                    <label class="form-label">Tahun Terbit</label>
                                    <input type="number" name="tahun_terbit" class="form-control form-control-sm" min="1900" max="<?= date('Y') ?>" required>
                                </div>
                                <div class="col-md-8 mt-3">
                                    <label class="form-label">Kategori</label><br>
                                    <?php foreach ($kategoris as $kategori): ?>
                                        <div class="form-check form-check-inline mb-1">
                                            <input class="form-check-input" type="checkbox" name="kategori_ids[]" id="kat<?= $kategori['id'] ?>" value="<?= $kategori['id'] ?>">
                                            <label class="form-check-label small" for="kat<?= $kategori['id'] ?>"><?= htmlspecialchars($kategori['nama']) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-12 mt-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control form-control-sm" rows="3"></textarea>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label class="form-label">Cover (Gambar)</label>
                                    <input type="file" name="cover" class="form-control form-control-sm" accept="image/*">
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label class="form-label">File E-Book (PDF)</label>
                                    <input type="file" name="file" class="form-control form-control-sm" accept=".pdf" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3 gap-2">
                                <button type="submit" class="btn btn-success btn-sm">Simpan</button>
                                <a href="index.php" class="btn btn-secondary btn-sm">Batal</a>
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