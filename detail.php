<?php
require_once 'config/database.php';

// Ambil ID buku dari parameter URL
$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$bookId) {
    header('Location: index.php');
    exit;
}

$db = getDBConnection();
$book = null;
$relatedBooks = [];

try {
    // Query untuk mendapatkan detail buku
    $stmt = $db->prepare("
        SELECT ebook.*, GROUP_CONCAT(kategori.nama SEPARATOR ', ') as kategori_nama 
        FROM ebook 
        LEFT JOIN ebook_kategori ON ebook.id = ebook_kategori.ebook_id 
        LEFT JOIN kategori ON ebook_kategori.kategori_id = kategori.id 
        WHERE ebook.id = ? 
        GROUP BY ebook.id
    ");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();

    if (!$book) {
        header('Location: index.php');
        exit;
    }

    // Query untuk mendapatkan buku terkait (dari kategori yang sama)
    $stmt = $db->prepare("
        SELECT DISTINCT ebook.* 
        FROM ebook 
        JOIN ebook_kategori ON ebook.id = ebook_kategori.ebook_id 
        WHERE ebook_kategori.kategori_id IN (
            SELECT kategori_id FROM ebook_kategori WHERE ebook_id = ?
        ) 
        AND ebook.id != ? 
        ORDER BY ebook.created_at DESC 
        LIMIT 4
    ");
    $stmt->execute([$bookId, $bookId]);
    $relatedBooks = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Gagal memuat data: " . $e->getMessage();
}
?>

<!-- Header -->
<?php include './includes/head.php'; ?>
<!-- /Header -->

<body class="">
    <div class="page">
        <div class="page-main">
            <div class="header py-4">

                <!-- Navbar -->
                <?php include './includes/navbar-user.php'; ?>
                <!-- / Navbar -->

                <div class="my-3 my-md-5">
                    <div class="container">
                        <div class="page-header text-center mb-4">
                            <h1 class="page-title fw-bold">
                                DETAIL E-BOOK PELAJARAN SEKOLAH MENENGAH PERTAMA
                            </h1>
                        </div>

                        <?php if ($book): ?>
                            <div class="row">
                                <!-- Kolom Gambar -->
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <?php if ($book['cover_url']): ?>
                                            <img src="uploads/covers/<?= htmlspecialchars($book['cover_url']); ?>"
                                                class="card-img-top mt-2 responsive-cover"
                                                alt="<?= htmlspecialchars($book['judul']); ?>">
                                        <?php else: ?>
                                            <img src="https://via.placeholder.com/300x400?text=No+Cover"
                                                class="card-img-top mt-2 responsive-cover"
                                                alt="Cover tidak tersedia">
                                        <?php endif; ?>

                                        <style>
                                            /* Ukuran default (desktop) */
                                            .responsive-cover {
                                                width: 100%;
                                                max-width: 300px;
                                                /* batas maksimal di desktop */
                                                height: auto;
                                                margin: 0 auto;
                                                display: block;
                                            }

                                            /* Ukuran lebih kecil di mobile */
                                            @media (max-width: 768px) {
                                                .responsive-cover {
                                                    max-width: 180px;
                                                    /* perkecil ukuran di mobile */
                                                }
                                            }
                                        </style>


                                        <div class="card-body text-center">
                                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                <a href="index.php" class="btn btn-secondary btn-lg">
                                                    <i class="fe fe-arrow-left"></i> Kembali
                                                </a>
                                                <a href="reader.php?id=<?= $book['id'] ?>" class="btn btn-outline-danger btn-lg">
                                                    <i class="fe fe-eye"></i> Baca Online
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Kolom Informasi -->
                                <div class="col-md-8">
                                    <div class="card shadow-sm">
                                        <div class="card-body">
                                            <h1 class="card-title h2 font-weight-bold text-primary">
                                                <?= htmlspecialchars($book['judul']); ?>
                                            </h1>

                                            <div class="mb-4">
                                                <span class="badge bg-secondary"><?= htmlspecialchars($book['kategori_nama']); ?></span>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <h5 class="text-muted">Penulis</h5>
                                                    <p class="h5"><?= htmlspecialchars($book['penulis']); ?></p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h5 class="text-muted">Tahun Terbit</h5>
                                                    <p class="h5"><?= htmlspecialchars($book['tahun_terbit']); ?></p>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <h5 class="text-muted">Deskripsi</h5>
                                                <p class="lead" style="text-align: justify; line-height: 1.6;">
                                                    <?= nl2br(htmlspecialchars($book['deskripsi'])); ?>
                                                </p>
                                            </div>

                                            <div class="row text-muted">
                                                <div class="col-md-6">
                                                    <small>ISBN: <?= htmlspecialchars($book['isbn'] ?: 'Tidak tersedia'); ?></small>
                                                </div>
                                                <div class="col-md-6 text-md-end">
                                                    <small>Ditambahkan: <?= date('d M Y', strtotime($book['created_at'])); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informasi File -->
                                    <div class="card shadow-sm mt-4">
                                        <div class="card-body">
                                            <h5 class="card-title">Informasi File</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Format:</strong> PDF</p>
                                                    <p class="mb-1"><strong>Ukuran:</strong>
                                                        <?php
                                                        $filePath = 'uploads/ebooks/' . $book['file_url'];
                                                        if (file_exists($filePath)) {
                                                            $fileSize = filesize($filePath);
                                                            echo round($fileSize / 1024 / 1024, 2) . ' MB';
                                                        } else {
                                                            echo 'Tidak diketahui';
                                                        }
                                                        ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Halaman:</strong> <?= htmlspecialchars($book['jumlah_halaman'] ?: 'Tidak tersedia'); ?></p>
                                                    <p class="mb-1"><strong>Penulis:</strong> <?= htmlspecialchars($book['penulis'] ?: 'Tidak tersedia'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buku Terkait -->
                            <?php if (!empty($relatedBooks)): ?>
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <h3 class="mb-4">Buku Terkait</h3>
                                        <div class="row">
                                            <?php foreach ($relatedBooks as $relatedBook): ?>
                                                <div class="col-6 col-md-3 mb-4">
                                                    <div class="card h-100">
                                                        <?php if ($relatedBook['cover_url']): ?>
                                                            <img src="uploads/covers/<?= htmlspecialchars($relatedBook['cover_url']); ?>"
                                                                class="card-img-top"
                                                                alt="<?= htmlspecialchars($relatedBook['judul']); ?>"
                                                                style="height: 200px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <img src="https://via.placeholder.com/150x200?text=No+Cover"
                                                                class="card-img-top"
                                                                alt="Cover tidak tersedia">
                                                        <?php endif; ?>

                                                        <div class="card-body d-flex flex-column">
                                                            <h6 class="card-title"><?= htmlspecialchars($relatedBook['judul']); ?></h6>
                                                            <p class="card-text small text-muted">
                                                                Oleh: <?= htmlspecialchars($relatedBook['penulis']); ?>
                                                            </p>
                                                            <div class="mt-auto">
                                                                <a href="detail.php?id=<?= $relatedBook['id'] ?>"
                                                                    class="btn btn-sm btn-outline-primary btn-block">
                                                                    Lihat Detail
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                <?php endif; ?>

                            <?php else: ?>
                                <div class="alert alert-danger text-center">
                                    <h4>Buku tidak ditemukan</h4>
                                    <p>Buku yang Anda cari tidak tersedia atau telah dihapus.</p>
                                    <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a>
                                </div>
                            <?php endif; ?>
                                </div>


                                <script>
                                    function toggleDescription(id, expand = true) {
                                        const shortDesc = document.getElementById(`desc-${id}`);
                                        const fullDesc = document.getElementById(`desc-full-${id}`);

                                        if (expand) {
                                            shortDesc.classList.add('d-none');
                                            fullDesc.classList.remove('d-none');
                                        } else {
                                            shortDesc.classList.remove('d-none');
                                            fullDesc.classList.add('d-none');
                                        }
                                    }
                                </script>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <div class="container">
                    <div class="row align-items-center flex-row-reverse">
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-center">
                            Copyright © 2025 erinfn <br> <a href=".">E-Book Pelajaran Sekolah Menengah Pertama</a>.
                        </div>
                    </div>
                </div>
            </footer>
        </div>
</body>

</html>