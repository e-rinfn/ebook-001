<?php
require_once 'config/database.php';

// Ambil ID buku dari URL
$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$bookId) {
    header('Location: index.php');
    exit;
}

$db = getDBConnection();
$book = null;

try {
    $stmt = $db->prepare("SELECT * FROM ebook WHERE id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        header('Location: index.php');
        exit;
    }

    $filePath = 'uploads/ebooks/' . $book['file_url'];
    if (!file_exists($filePath)) {
        $error = "File eBook tidak ditemukan.";
    }
} catch (PDOException $e) {
    $error = "Gagal memuat data: " . $e->getMessage();
}
?>

<?php include './includes/head.php'; ?>

<style>
    body {
        margin: 0;
        background: #1f1f1f;
        color: white;
        font-family: 'Segoe UI', sans-serif;
        overflow: hidden;
    }

    .reader-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }

    /* Header Atas */
    .reader-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #2b2b2b;
        border-bottom: 1px solid #444;
    }

    .book-info {
        flex: 1;
    }

    .book-title {
        font-weight: 600;
        font-size: 14px;
        margin: 0;
    }

    .book-author {
        font-size: 12px;
        color: #b5b5b5;
        margin: 0;
    }

    .reader-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-control {
        background: #3c3c3c;
        color: #fff;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: background 0.2s;
    }

    .btn-control:hover {
        background: #4d4d4d;
    }

    .zoom-info {
        font-size: 13px;
        color: #aaa;
        margin-right: 8px;
    }

    /* Area PDF */
    .reader-content {
        flex: 1;
        background: #000;
    }

    .pdf-viewer {
        width: 100%;
        height: 100%;
        border: none;
        background: #fff;
    }

    /* Tampilan fullscreen */
    .fullscreen-mode .reader-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .fullscreen-mode .reader-header:hover {
        opacity: 1;
    }

    /* Pesan error */
    .error-box {
        text-align: center;
        padding: 50px;
    }

    .error-box h4 {
        color: #ff5555;
    }

    .error-box button {
        background: #444;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        margin-top: 15px;
        cursor: pointer;
    }

    .error-box button:hover {
        background: #555;
    }
</style>

<body>
    <div class="reader-container" id="readerContainer">

        <!-- Header -->
        <div class="reader-header">
            <div class="book-info">
                <p class="book-title"><?= htmlspecialchars($book['judul']); ?></p>
                <p class="book-author">Oleh: <?= htmlspecialchars($book['penulis']); ?></p>
            </div>

            <div class="reader-controls">
                <span id="zoomLevel" class="zoom-info">100%</span>
                <button class="btn-control" onclick="zoomOut()" title="Perkecil (Ctrl -)">−</button>
                <button class="btn-control" onclick="zoomIn()" title="Perbesar (Ctrl +)">+</button>
                <button class="btn-control" onclick="window.history.back()" title="Kembali">Kembali</button>
            </div>
        </div>

        <!-- Konten -->
        <div class="reader-content">
            <?php if (isset($error)): ?>
                <div style="color: white; text-align: center; padding: 50px;">
                    <h4>File tidak dapat dimuat</h4>
                    <p><?= $error ?></p>
                    <button class="btn-control" onclick="window.history.back()">Kembali</button>
                </div>
            <?php else: ?>
                <!-- Tampilkan PDF di iframe -->
                <iframe
                    src="uploads/ebooks/<?= htmlspecialchars($book['file_url']); ?>#toolbar=0"
                    class="pdf-viewer"
                    id="pdfViewer"
                    title="<?= htmlspecialchars($book['judul']); ?>">
                </iframe>

                <!-- Fallback jika PDF gagal dimuat -->
                <div id="pdfFallback" style="display:none; text-align:center; padding:20px; color:white;">
                    <p>📄 PDF tidak dapat ditampilkan di perangkat ini.</p>
                    <a href="uploads/ebooks/<?= htmlspecialchars($book['file_url']); ?>"
                        target="_blank"
                        class="btn-control">Buka di Tab Baru</a>
                </div>
            <?php endif; ?>
        </div>

        <script>
            // Script fallback otomatis untuk Android / browser tanpa PDF viewer
            window.addEventListener('load', () => {
                const iframe = document.getElementById('pdfViewer');
                const fallback = document.getElementById('pdfFallback');

                // Jika setelah 2 detik iframe belum bisa dimuat, tampilkan fallback
                setTimeout(() => {
                    if (iframe && iframe.contentDocument === null) {
                        iframe.style.display = 'none';
                        fallback.style.display = 'block';
                    }
                }, 2000);
            });
        </script>
    </div>

    <script>
        let currentScale = 1.0;
        const zoomStep = 0.1;

        function updateZoom() {
            const iframe = document.getElementById('pdfViewer');
            const zoomLevel = document.getElementById('zoomLevel');
            iframe.style.transform = `scale(${currentScale})`;
            iframe.style.transformOrigin = '0 0';
            iframe.style.width = `${100 / currentScale}%`;
            iframe.style.height = `${100 / currentScale}%`;
            zoomLevel.textContent = `${Math.round(currentScale * 100)}%`;
        }

        function zoomIn() {
            currentScale += zoomStep;
            if (currentScale > 2.5) currentScale = 2.5;
            updateZoom();
        }

        function zoomOut() {
            currentScale -= zoomStep;
            if (currentScale < 0.5) currentScale = 0.5;
            updateZoom();
        }

        // Shortcut keyboard
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === '+' || e.key === '=')) {
                e.preventDefault();
                zoomIn();
            } else if (e.ctrlKey && e.key === '-') {
                e.preventDefault();
                zoomOut();
            } else if (e.key === 'Escape') {
                window.history.back();
            }
        });
    </script>
</body>

</html>