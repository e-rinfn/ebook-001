<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$search = $_GET['search'] ?? '';
$query = "SELECT ebook.*, GROUP_CONCAT(kategori.nama SEPARATOR ', ') as kategori_nama 
          FROM ebook 
          LEFT JOIN ebook_kategori ON ebook.id = ebook_kategori.ebook_id 
          LEFT JOIN kategori ON ebook_kategori.kategori_id = kategori.id 
          WHERE ebook.judul LIKE ? OR ebook.penulis LIKE ?
          GROUP BY ebook.id";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%"]);
$ebooks = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
                                DAFTAR E-BOOK
                            </h1>
                        </div>

                        <div class="action-bar mb-3 d-flex flex-wrap justify-content-between align-items-center">
                            <form method="GET" class="form-inline mb-2 mb-md-0">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari judul atau penulis..." value="<?= htmlspecialchars($search ?? '') ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Cari</button>
                                        <a href="index.php" class="btn btn-warning">Reset</a>
                                    </div>
                                </div>
                            </form>
                            <a href="add.php" class="btn btn-success ml-md-2">Tambah E-Book</a>
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


                        <div class="row row-cards row-deck">
                            <div class="col-12">
                                <div class="card">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-outline table-vcenter text-nowrap card-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th> <!-- Tambahkan ini -->
                                                    <th>Cover</th>
                                                    <th>Judul</th>
                                                    <th>Penulis</th>
                                                    <th>Kategori</th>
                                                    <th class="text-center">Tahun</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                foreach ($ebooks as $ebook): ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td> <!-- Tambahkan ini -->
                                                        <td>
                                                            <?php if ($ebook['cover_url']): ?>
                                                                <a href="../../uploads/covers/<?= htmlspecialchars($ebook['cover_url']) ?>" target="_blank" rel="noopener noreferrer">
                                                                    <img src="../../uploads/covers/<?= htmlspecialchars($ebook['cover_url']) ?>" alt="Cover" width="50">
                                                                </a>
                                                            <?php else: ?>
                                                                <div class="no-cover">No Cover</div>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td><?= htmlspecialchars($ebook['judul']) ?></td>
                                                        <td><?= htmlspecialchars($ebook['penulis']) ?></td>
                                                        <td><?= $ebook['kategori_nama'] ?? '-' ?></td>
                                                        <td class="text-center"><?= $ebook['tahun_terbit'] ?></td>
                                                        <td class="text-center">
                                                            <a href="edit.php?id=<?= $ebook['id'] ?>" class="btn btn-primary btn-edit">Edit</a>
                                                            <a href="delete.php?id=<?= $ebook['id'] ?>" class="btn btn-danger btn-delete" onclick="return confirm('Yakin hapus e-book ini?')">Hapus</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>

                        </div>
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
</body>

</html>