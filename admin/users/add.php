<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/functions.php';

// Hanya super_admin yang bisa akses
$required_role = 'super_admin';
require_once '../../includes/auth-check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi
    if (empty($nama) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Semua field wajib diisi";
    } else {
        // Cek email sudah ada
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email sudah terdaftar";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database
            $stmt = $pdo->prepare("INSERT INTO admin (nama, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $email, $hashed_password, $role]);

            $_SESSION['success'] = "Pengguna berhasil ditambahkan";
            redirect('index.php?success=add');
        }
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
                                TAMBAH KATEGORI
                            </h1>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <?php if (isset($_GET['success'])): ?>
                                    <div class="alert alert-success">
                                        E-Book berhasil <?= $_GET['success'] === 'add' ? 'ditambahkan' : 'diperbarui' ?>!
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                                    <?php unset($_SESSION['error']); ?>
                                <?php endif; ?>

                                <form method="POST" class="user-form">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" name="nama" id="nama" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" name="password" id="password" class="form-control" required minlength="6">
                                    </div>

                                    <div class="form-group w-25">
                                        <label for="role">Role</label>
                                        <select name="role" id="role" class="form-control" required>
                                            <option value="" disabled selected>Pilih Role</option>
                                            <option value="admin">Admin</option>
                                            <option value="super_admin">Super Admin</option>
                                        </select>
                                    </div>


                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-primary">Simpan</button>
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