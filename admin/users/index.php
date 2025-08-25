<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

// Hanya super_admin yang bisa akses
$required_role = 'super_admin';
require_once '../../includes/auth-check.php';

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM admin WHERE nama LIKE ? OR email LIKE ?";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%"]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                                DAFTAR PENGGUNA
                            </h1>
                        </div>

                        <div class="action-bar mb-3 d-flex flex-wrap justify-content-between align-items-center">
                            <form method="GET" class="form-inline mb-2 mb-md-0">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Cari pengguna..." value="<?= htmlspecialchars($search ?? '') ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Cari</button>
                                        <a href="index.php" class="btn btn-warning">Reset</a>
                                    </div>
                                </div>
                            </form>
                            <a href="add.php" class="btn btn-success ml-md-2">Tambah Pengguna</a>
                        </div>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                Pengguna berhasil <?= $_GET['success'] === 'add' ? 'ditambahkan' : 'diperbarui' ?>!
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
                                                    <th class="text-center">No</th> <!-- Tambahkan ini -->
                                                    <th class="text-center">Nama</th>
                                                    <th class="text-center">Email</th>
                                                    <th class="text-center">Role</th>
                                                    <th class="text-center">Tanggal Daftar</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                foreach ($users as $user): ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td> <!-- Tambahkan ini -->
                                                        <td><?= htmlspecialchars($user['nama']) ?></td>
                                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                                        <td class="text-center">
                                                            <?php if ($user['role'] === 'super_admin'): ?>
                                                                <span class="badge badge-success">Super Admin</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-primary">Admin</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="text-center"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                                        <td class="text-center">
                                                            <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-edit">Edit</a>
                                                            <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                                <a href="delete.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-delete" onclick="return confirm('Yakin hapus pengguna ini?')">Hapus</a>
                                                            <?php endif; ?>
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