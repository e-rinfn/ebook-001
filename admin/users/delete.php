<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

// Hanya super_admin yang bisa akses
$required_role = 'super_admin';
require_once '../../includes/auth-check.php';

$id = $_GET['id'] ?? 0;

// Cek apakah mencoba menghapus diri sendiri
if ($id == $_SESSION['admin_id']) {
    $_SESSION['error'] = "Anda tidak dapat menghapus akun sendiri";
    redirect('index.php');
}

// Hapus user
$stmt = $pdo->prepare("DELETE FROM admin WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['success'] = "Pengguna berhasil dihapus";
redirect('index.php');
