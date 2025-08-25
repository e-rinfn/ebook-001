<?php
require_once '../../config/database.php';
require_once '../../includes/auth-check.php';
require_once '../../includes/functions.php';

$id = $_GET['id'] ?? 0;

// Ambil data ebook untuk menghapus file
$ebook = $pdo->prepare("SELECT cover_url, file_url FROM ebook WHERE id = ?");
$ebook->execute([$id]);
$ebook = $ebook->fetch(PDO::FETCH_ASSOC);

if ($ebook) {
    // Hapus file cover jika ada
    if ($ebook['cover_url'] && file_exists("../../uploads/covers/{$ebook['cover_url']}")) {
        unlink("../../uploads/covers/{$ebook['cover_url']}");
    }

    // Hapus file ebook jika ada
    if ($ebook['file_url'] && file_exists("../../uploads/ebooks/{$ebook['file_url']}")) {
        unlink("../../uploads/ebooks/{$ebook['file_url']}");
    }

    // Hapus dari database
    $pdo->prepare("DELETE FROM ebook WHERE id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM ebook_kategori WHERE ebook_id = ?")->execute([$id]);
}

redirect('index.php');
