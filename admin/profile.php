<?php
require_once '../config/database.php';
require_once '../includes/auth-check.php';
require_once '../includes/functions.php';

$admin_id = $_SESSION['admin_id'];
$admin = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$admin->execute([$admin_id]);
$admin = $admin->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin SET nama = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $hashed_password, $admin_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE admin SET nama = ?, email = ? WHERE id = ?");
        $stmt->execute([$nama, $email, $admin_id]);
    }

    $_SESSION['admin_nama'] = $nama;
    $_SESSION['success'] = "Profil berhasil diperbarui";
    redirect('profile.php');
}

?>

<!-- Header -->
<?php include '../includes/head.php'; ?>
<!-- /Header -->

<body class="">
    <div class="page">
        <div class="page-main">
            <div class="header py-4">

                <!-- Navbar -->
                <?php include '../includes/navbar.php'; ?>
                <!-- / Navbar -->

                <div class="my-3 my-md-5">
                    <div class="container">
                        <div class="page-header">
                            <h1 class="page-title">
                                PROFILE PENGGUNA
                            </h1>
                        </div>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Edit Profil Admin</h5>
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($admin['nama']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <small class="text-muted">(Biarkan kosong jika tidak ingin mengubah)</small></label>
                                        <input type="password" class="form-control" id="password" name="password" minlength="6">
                                    </div>

                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="6">
                                    </div>

                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </form>
                            </div>
                        </div>

                        <script>
                            // Validasi sederhana untuk konfirmasi password
                            document.querySelector('form').addEventListener('submit', function(e) {
                                const password = document.getElementById('password').value;
                                const confirm = document.getElementById('confirm_password').value;
                                if (password !== '' && password !== confirm) {
                                    e.preventDefault();
                                    alert('Password dan konfirmasi password tidak cocok.');
                                }
                            });
                        </script>


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