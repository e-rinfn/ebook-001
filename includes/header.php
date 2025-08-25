<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengelolaan E-Book Sekolah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header>
        <h1>Sistem Pengelolaan E-Book</h1>
    </header>

    <nav>
        <ul>
            <li><a href="/ebook/admin/dashboard.php">Dashboard</a></li>
            <li><a href="/ebook/admin/ebook/index.php">E-Book</a></li>
            <li><a href="/ebook/admin/kategori/index.php">Kategori</a></li>
            <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
                <li><a href="/ebook/admin/users/index.php">Kelola Pengguna</a></li>
            <?php endif; ?>
            <li><a href="/ebook/admin/profile.php">Profil</a></li>
            <li><a href="/ebook/auth/logout.php">Logout</a></li>
        </ul>
    </nav>

    <main class="container"></main>