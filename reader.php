<?php
require_once 'config/database.php';

// Ambil ID buku dari parameter URL
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
    $book = $stmt->fetch();

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

<!-- Header -->
<?php include './includes/head.php'; ?>
<!-- /Header -->

<style>
    body {
        margin: 0;
        padding: 0;
        background: #2c2c2c;
        overflow: hidden;
    }

    .reader-container {
        height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .reader-header {
        background: #1a1a1a;
        color: white;
        padding: 10px 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        border-bottom: 1px solid #444;
    }

    .book-info {
        flex: 1;
    }

    .book-title {
        font-size: 14px;
        font-weight: bold;
        margin: 0;
    }

    .book-author {
        font-size: 12px;
        color: #ccc;
        margin: 0;
    }

    .reader-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .btn-control {
        background: #444;
        border: none;
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }

    .btn-control:hover {
        background: #555;
    }

    .reader-content {
        flex: 1;
        background: #2c2c2c;
    }

    .pdf-viewer {
        width: 100%;
        height: 100%;
        border: none;
        background: white;
    }

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

    .zoom-info {
        font-size: 12px;
        color: #ccc;
        margin: 0 10px;
    }

    /* Minimal controls for better focus */
    .controls-minimal {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        gap: 8px;
        z-index: 1000;
    }

    .control-mini {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
    }
</style>

<body>
    <div class="reader-container" id="readerContainer">

        <!-- Simple Header -->
        <div class="reader-header">
            <div class="book-info">
                <div class="book-title"><?= htmlspecialchars($book['judul']); ?></div>
                <div class="book-author">Oleh: <?= htmlspecialchars($book['penulis']); ?></div>
            </div>

            <div class="reader-controls">
                <span class="zoom-info" id="zoomLevel">100%</span>
                <button class="btn-control" onclick="zoomOut()" title="Zoom Out">
                    <i class="fe fe-zoom-out"></i>
                </button>
                <button class="btn-control" onclick="zoomIn()" title="Zoom In">
                    <i class="fe fe-zoom-in"></i>
                </button>
                <!-- <button class="btn-control" onclick="toggleFullscreen()" title="Layar Penuh (F11)">
          <i class="fe fe-maximize"></i>
        </button> -->
                <button class="btn-control" onclick="window.location.href='detail.php?id=<?= $bookId ?>'" title="Kembali">
                    <i class="fe fe-x"></i>
                </button>
            </div>
        </div>

        <!-- PDF Content -->
        <div class="reader-content">
            <?php if (isset($error)): ?>
                <div style="color: white; text-align: center; padding: 50px;">
                    <h4>File tidak dapat dimuat</h4>
                    <p><?= $error ?></p>
                    <button class="btn-control" onclick="window.location.href='detail.php?id=<?= $bookId ?>'">
                        Kembali ke Detail
                    </button>
                </div>
            <?php else: ?>
                <iframe src="uploads/ebooks/<?= htmlspecialchars($book['file_url']); ?>#toolbar=0&navpanes=0&scrollbar=0"
                    class="pdf-viewer"
                    id="pdfViewer"
                    title="<?= htmlspecialchars($book['judul']); ?>">
                </iframe>
            <?php endif; ?>
        </div>

        <!-- Minimal Floating Controls -->
        <!-- <div class="controls-minimal">
      <button class="control-mini" onclick="previousPage()" title="Halaman Sebelumnya (←)">
        <i class="fe fe-chevron-left"></i>
      </button>
      <button class="control-mini" onclick="nextPage()" title="Halaman Berikutnya (→)">
        <i class="fe fe-chevron-right"></i>
      </button>
    </div> -->
    </div>

    <script>
        let currentScale = 1;
        const zoomStep = 0.25;

        function zoomIn() {
            currentScale += zoomStep;
            updateZoom();
        }

        function zoomOut() {
            if (currentScale > 0.5) {
                currentScale -= zoomStep;
                updateZoom();
            }
        }

        function updateZoom() {
            const iframe = document.getElementById('pdfViewer');
            const zoomLevel = document.getElementById('zoomLevel');

            iframe.style.transform = `scale(${currentScale})`;
            iframe.style.transformOrigin = '0 0';
            iframe.style.width = `${100 / currentScale}%`;
            iframe.style.height = `${100 / currentScale}%`;

            zoomLevel.textContent = `${Math.round(currentScale * 100)}%`;
        }

        function previousPage() {
            const iframe = document.getElementById('pdfViewer');
            try {
                iframe.contentWindow.PDFViewerApplication.page--;
            } catch (e) {
                // Fallback jika PDF.js tidak tersedia
            }
        }

        function nextPage() {
            const iframe = document.getElementById('pdfViewer');
            try {
                iframe.contentWindow.PDFViewerApplication.page++;
            } catch (e) {
                // Fallback jika PDF.js tidak tersedia
            }
        }

        function toggleFullscreen() {
            const container = document.getElementById('readerContainer');

            if (!document.fullscreenElement) {
                if (container.requestFullscreen) {
                    container.requestFullscreen();
                } else if (container.webkitRequestFullscreen) {
                    container.webkitRequestFullscreen();
                }
                container.classList.add('fullscreen-mode');
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
                container.classList.remove('fullscreen-mode');
            }
        }

        // Keyboard shortcuts for better reading experience
        document.addEventListener('keydown', function(e) {
            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    previousPage();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    nextPage();
                    break;
                case 'F11':
                    e.preventDefault();
                    toggleFullscreen();
                    break;
                case 'Escape':
                    if (document.fullscreenElement) {
                        toggleFullscreen();
                    }
                    break;
                case '+':
                case '=':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        zoomIn();
                    }
                    break;
                case '-':
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        zoomOut();
                    }
                    break;
            }
        });

        // Auto-hide header in fullscreen after 2 seconds
        let hideHeaderTimeout;
        document.addEventListener('mousemove', function() {
            const header = document.querySelector('.reader-header');
            if (document.fullscreenElement && header) {
                header.style.opacity = '1';
                clearTimeout(hideHeaderTimeout);
                hideHeaderTimeout = setTimeout(() => {
                    header.style.opacity = '0';
                }, 2000);
            }
        });

        // Fit to width on initial load
        window.addEventListener('load', function() {
            setTimeout(() => {
                const iframe = document.getElementById('pdfViewer');
                const container = document.querySelector('.reader-content');
                if (iframe && container) {
                    const containerWidth = container.clientWidth;
                    currentScale = containerWidth / iframe.scrollWidth;
                    updateZoom();
                }
            }, 1000);
        });
    </script>
</body>

</html>