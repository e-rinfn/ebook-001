<?php
require_once '../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    // Validasi input
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi";
    } else {
        // Cek admin di database
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Buat session
            session_start();
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_email'] = $admin['email'];

            // Redirect ke dashboard
            header('Location: ../admin/dashboard.php');
            exit();
        } else {
            $error = "Email atau password salah";
        }
    }
}

?>

<!-- Header -->
<?php include '../includes/head.php'; ?>
<!-- /Header -->

<body>
    <div class="container d-flex justify-content-center align-items-center " style="min-height: 100vh;">
        <div class="row shadow-lg rounded overflow-hidden w-100 border" style="max-width: 900px;">
            <!-- Kolom Gambar -->
            <div class="col-md-6 d-none d-md-block p-0">
                <img src="<?= $base_url ?>/assets/images/login-image.jpg" alt="Login Image"
                    class="img-fluid h-100 w-100" style="object-fit: cover;">
            </div>

            <!-- Kolom Form Login -->
            <div class="col-md-6 bg-white p-4">
                <div class="text-center mb-3">
                    <img src="<?= $base_url ?>/assets/images/Logo.png" alt="Login Illustration" style="height: 150px;">
                </div>
                <h1 class="text-center font-weight-bold mb-3 text-dark">MASUK</h1>
                <p class="text-center mb-4">Masuk ke akun Anda untuk mengelola sistem.</p>
                <hr>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="email" class="form-control-label">Email</label>
                        <input type="email"
                            class="form-control"
                            placeholder="Masukkan email"
                            id="email"
                            name="email"
                            required
                            value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                    </div>

                    <div class="form-group mb-4">
                        <label for="password" class="form-control-label">Password</label>
                        <input type="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            id="password"
                            name="password"
                            required>
                    </div>

                    <button type="submit" class="btn btn-danger btn-block">Masuk</button>
                </form>

            </div>
        </div>
    </div>
</body>

</html>