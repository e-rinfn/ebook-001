<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';

// Pastikan hanya super_admin yang bisa menghapus
$required_role = 'super_admin';
require_once '../../includes/auth-check.php';

// Ambil ID user dari URL dan pastikan berupa angka
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Jika ID tidak valid
if ($id <= 0) {
    $_SESSION['error'] = "ID pengguna tidak valid.";
    header("Location: index.php");
    exit;
}

// Cegah menghapus akun sendiri
if ($id == $_SESSION['admin_id']) {
    $_SESSION['error'] = "Anda tidak dapat menghapus akun sendiri.";
    header("Location: index.php");
    exit;
}

// Cek apakah user dengan ID tersebut ada di database
$check = $pdo->prepare("SELECT id FROM admin WHERE id = ?");
$check->execute([$id]);
$user = $check->fetch();

if (!$user) {
    $_SESSION['error'] = "Pengguna tidak ditemukan.";
    header("Location: index.php");
    exit;
}

// Hapus pengguna
$stmt = $pdo->prepare("DELETE FROM admin WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['success'] = "Pengguna berhasil dihapus.";
header("Location: index.php");
exit;
