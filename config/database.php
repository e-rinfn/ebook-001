<?php
// Konfigurasi koneksi database
define('DB_HOST', 'localhost');
define('DB_NAME', 'ebook-safa');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // Membuat koneksi PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // echo "Koneksi database berhasil!";
} catch (PDOException $e) {
    // Jika terjadi error, tampilkan pesan error
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk mendapatkan koneksi database
function getDBConnection()
{
    global $pdo;
    return $pdo;
}
