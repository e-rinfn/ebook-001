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
        -webkit-text-size-adjust: 100%;
        touch-action: manipulation;
    }

    .reader-container {
        height: 100vh;
        height: 100dvh;
        /* Untuk browser modern */
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
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
        z-index: 1000;
    }

    .book-info {
        flex: 1;
        min-width: 0;
    }

    .book-title {
        font-size: 14px;
        font-weight: bold;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .book-author {
        font-size: 12px;
        color: #ccc;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .reader-controls {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
    }

    .btn-control {
        background: #444;
        border: none;
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        -webkit-tap-highlight-color: transparent;
        min-height: 36px;
    }

    .btn-control:active {
        background: #555;
        transform: scale(0.95);
    }

    .reader-content {
        flex: 1;
        background: #2c2c2c;
        position: relative;
        overflow: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .pdf-viewer {
        width: 100%;
        height: 100%;
        border: none;
        background: white;
        display: block;
    }

    .mobile-message {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        z-index: 1000;
        max-width: 90%;
    }

    .zoom-info {
        font-size: 12px;
        color: #ccc;
        margin: 0 10px;
        display: none;
        /* Sembunyikan di mobile */
    }

    /* Touch controls untuk mobile */
    .touch-controls {
        position: fixed;
        bottom: 20px;
        right: 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        z-index: 1000;
    }

    .touch-btn {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        -webkit-tap-highlight-color: transparent;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .touch-btn:active {
        background: rgba(0, 0, 0, 0.9);
        transform: scale(0.95);
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .reader-header {
            padding: 8px 12px;
        }

        .book-title {
            font-size: 13px;
        }

        .book-author {
            font-size: 11px;
        }

        .btn-control {
            padding: 6px 10px;
            font-size: 12px;
            min-height: 32px;
        }

        .reader-controls {
            gap: 5px;
        }

        .zoom-info {
            display: none;
            /* Sembunyikan zoom info di mobile */
        }
    }

    @media (max-width: 480px) {
        .book-title {
            font-size: 12px;
        }

        .book-author {
            display: none;
            /* Sembunyikan author di layar sangat kecil */
        }

        .btn-control span {
            display: none;
            /* Sembunyikan teks, hanya icon */
        }

        .btn-control {
            padding: 8px;
            min-width: 40px;
        }
    }

    /* Loading indicator */
    .loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 16px;
    }

    /* Error message */
    .error-message {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        max-width: 90%;
        z-index: 1000;
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
                    <span style="margin-left: 5px;">Zoom Out</span>
                </button>
                <button class="btn-control" onclick="zoomIn()" title="Zoom In">
                    <i class="fe fe-zoom-in"></i>
                    <span style="margin-left: 5px;">Zoom In</span>
                </button>
                <button class="btn-control" onclick="window.history.back()" title="Kembali">
                    <i class="fe fe-arrow-left"></i>
                    <span style="margin-left: 5px;">Kembali</span>
                </button>
            </div>
        </div>

        <!-- PDF Content -->
        <div class="reader-content">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <h4>File tidak dapat dimuat</h4>
                    <p><?= $error ?></p>
                    <button class="btn-control" onclick="window.history.back()" style="margin-top: 15px;">
                        Kembali
                    </button>
                </div>
            <?php else: ?>
                <div class="loading" id="loadingIndicator">Memuat eBook...</div>
                <iframe
                    src="uploads/ebooks/<?= htmlspecialchars($book['file_url']); ?>#toolbar=0&navpanes=0&scrollbar=0"
                    class="pdf-viewer"
                    id="pdfViewer"
                    title="<?= htmlspecialchars($book['judul']); ?>"
                    onload="hideLoading()"
                    style="display: none;">
                </iframe>

                <!-- Fallback untuk mobile -->
                <!-- <div class="mobile-message" id="mobileMessage">
                    <h4>EBook Siap Dibaca</h4>
                    <p>Gunakan gesture pinch untuk zoom in/out</p>
                    <p>Swipe untuk navigasi halaman</p>
                    <button class="touch-btn" onclick="closeMobileMessage()" style="margin-top: 15px;">
                        Mulai Baca
                    </button>
                </div> -->
            <?php endif; ?>
        </div>

        <!-- Touch Controls untuk Mobile -->
        <div class="touch-controls">
            <!-- <button class="touch-btn" onclick="previousPage()" title="Halaman Sebelumnya">
                <i class="fe fe-chevron-left"></i>
            </button>
            <button class="touch-btn" onclick="nextPage()" title="Halaman Berikutnya">
                <i class="fe fe-chevron-right"></i>
            </button> -->
            <button class="touch-btn" onclick="downloadPDF()" title="Download">
                <i class="fe fe-download"></i>
            </button>
        </div>
    </div>

    <script>
        // Deteksi perangkat mobile
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        let currentScale = 1;

        function hideLoading() {
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('pdfViewer').style.display = 'block';

            // Tampilkan pesan mobile hanya di perangkat mobile
            if (isMobile) {
                setTimeout(() => {
                    document.getElementById('mobileMessage').style.display = 'block';
                }, 1000);
            }
        }

        function closeMobileMessage() {
            document.getElementById('mobileMessage').style.display = 'none';
        }

        function zoomIn() {
            currentScale += 0.25;
            updateZoom();
        }

        function zoomOut() {
            if (currentScale > 0.5) {
                currentScale -= 0.25;
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
                if (iframe.contentWindow.PDFViewerApplication) {
                    iframe.contentWindow.PDFViewerApplication.page--;
                } else {
                    // Fallback untuk browser mobile
                    showToast('Gunakan swipe ke kanan untuk halaman sebelumnya');
                }
            } catch (e) {
                showToast('Gunakan swipe ke kanan untuk halaman sebelumnya');
            }
        }

        function nextPage() {
            const iframe = document.getElementById('pdfViewer');
            try {
                if (iframe.contentWindow.PDFViewerApplication) {
                    iframe.contentWindow.PDFViewerApplication.page++;
                } else {
                    // Fallback untuk browser mobile
                    showToast('Gunakan swipe ke kiri untuk halaman berikutnya');
                }
            } catch (e) {
                showToast('Gunakan swipe ke kiri untuk halaman berikutnya');
            }
        }

        function downloadPDF() {
            const link = document.createElement('a');
            link.href = 'uploads/ebooks/<?= htmlspecialchars($book['file_url']); ?>';
            link.download = '<?= htmlspecialchars($book['judul']); ?>.pdf';
            link.click();
        }

        function showToast(message) {
            // Buat toast notification sederhana
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                bottom: 100px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 10px 20px;
                border-radius: 20px;
                z-index: 10000;
                font-size: 14px;
                white-space: nowrap;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                document.body.removeChild(toast);
            }, 2000);
        }

        // Handle orientation change
        window.addEventListener('orientationchange', function() {
            setTimeout(() => {
                // Reset zoom saat orientasi berubah
                currentScale = 1;
                updateZoom();
            }, 300);
        });

        // Prevent zoom dengan pinch di mobile
        document.addEventListener('touchmove', function(e) {
            if (e.scale !== 1) {
                e.preventDefault();
            }
        }, {
            passive: false
        });

        // Fit to width on initial load
        window.addEventListener('load', function() {
            setTimeout(() => {
                if (!isMobile) {
                    // Hanya auto-zoom di desktop
                    const iframe = document.getElementById('pdfViewer');
                    const container = document.querySelector('.reader-content');
                    if (iframe && container) {
                        const containerWidth = container.clientWidth;
                        if (iframe.scrollWidth > 0) {
                            currentScale = containerWidth / iframe.scrollWidth;
                            updateZoom();
                        }
                    }
                }
            }, 1500);
        });

        // Handle resize
        window.addEventListener('resize', function() {
            if (!isMobile) {
                updateZoom();
            }
        });

        // Tambahkan meta viewport secara dinamis untuk mobile
        if (isMobile) {
            const viewport = document.createElement('meta');
            viewport.name = 'viewport';
            viewport.content = 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no';
            document.head.appendChild(viewport);
        }
    </script>
</body>

</html>