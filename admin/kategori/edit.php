<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$id = $_GET['id'] ?? 0;
$kategori = $pdo->prepare("SELECT * FROM kategori WHERE id = ?");
$kategori->execute([$id]);
$kategori = $kategori->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];

    $stmt = $pdo->prepare("UPDATE kategori SET nama = ?, deskripsi = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$nama, $deskripsi, $id]);

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
                                EDIT KATEGORI
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

                        <div class="card">
                            <div class="card-body">
                                <form method="POST" class="kategori-form">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Kategori</label>
                                        <input type="text" name="nama" id="nama" class="form-control"
                                            value="<?= htmlspecialchars($kategori['nama']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label">Deskripsi</label>
                                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($kategori['deskripsi']) ?></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-warning">Update</button>
                                        <a href="index.php" class="btn btn-secondary">Batal</a>
                                    </div>
                                </form>
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