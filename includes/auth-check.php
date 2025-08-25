<?php
// session_start();

// // Redirect ke halaman login jika tidak ada session admin
// if (!isset($_SESSION['admin_id'])) {
//     $_SESSION['error'] = "Anda harus login terlebih dahulu";
//     header('Location: /ebook/auth/login.php');
//     exit();
// }

// // Cek role jika diperlukan (untuk halaman khusus super admin)
// $allowed_roles = ['super_admin'];
// if (isset($required_role) && !in_array($_SESSION['admin_role'], $allowed_roles)) {
//     $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
//     header('Location: /ebook/admin/dashboard.php');
//     exit();
// }


session_start();

// Redirect ke halaman login jika tidak ada session admin
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Anda harus login terlebih dahulu";
    header('Location: /../auth/login.php');
    exit();
}

// Cek role jika diperlukan (untuk halaman khusus super admin)
if (isset($required_role) && $required_role === 'super_admin' && $_SESSION['admin_role'] !== 'super_admin') {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini";
    header('Location: /../admin/dashboard.php');
    exit();
}
