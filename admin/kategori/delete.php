<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$id = $_GET['id'] ?? 0;

// Cek apakah kategori digunakan oleh ebook
$used = $pdo->prepare("SELECT COUNT(*) FROM ebook_kategori WHERE kategori_id = ?");
$used->execute([$id]);
$used = $used->fetchColumn();

if ($used > 0) {
    $_SESSION['error'] = "Kategori tidak bisa dihapus karena masih digunakan oleh beberapa e-book";
    redirect('index.php');
}

// Hapus kategori
$pdo->prepare("DELETE FROM kategori WHERE id = ?")->execute([$id]);

redirect('index.php');
