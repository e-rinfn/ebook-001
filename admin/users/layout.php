<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once __DIR__ . '/../../includes/functions.php';

// Hanya super_admin yang bisa akses
$required_role = 'super_admin';
require_once '../../includes/auth-check.php';

$id = $_GET['id'] ?? 0;
$user = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$user->execute([$id]);
$user = $user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi
    if (empty($nama) || empty($email)) {
        $_SESSION['error'] = "Nama dan email wajib diisi";
    } else {
        // Cek email sudah ada (kecuali untuk user ini)
        $stmt = $pdo->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email sudah terdaftar";
        } else {
            // Jika password diisi, update password
            $password_update = '';
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $password_update = ", password = '$hashed_password'";
            }

            // Update user
            $stmt = $pdo->prepare("UPDATE admin SET 
                                 nama = ?, email = ?, role = ? $password_update 
                                 WHERE id = ?");
            $stmt->execute([$nama, $email, $role, $id]);

            $_SESSION['success'] = "Pengguna berhasil diperbarui";
            redirect('index.php?success=edit');
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
                                EDIT KATEGORI
                            </h1>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                                    <?php unset($_SESSION['error']); ?>
                                <?php endif; ?>

                                <form method="POST" class="user-form">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap</label>
                                        <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password <small class="text-muted">(Biarkan kosong jika tidak ingin mengubah)</small></label>
                                        <input type="password" name="password" id="password" class="form-control" minlength="6">
                                    </div>

                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role</label>
                                        <select name="role" id="role" class="form-select" required>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="super_admin" <?= $user['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin</option>
                                        </select>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-primary">Update</button>
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