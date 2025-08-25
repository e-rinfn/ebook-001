<?php
require_once '../config/database.php';
require_once '../includes/auth-check.php';
require_once '../includes/functions.php';

$totalEbooks = $pdo->query("SELECT COUNT(*) FROM ebook")->fetchColumn();
$totalKategoris = $pdo->query("SELECT COUNT(*) FROM kategori")->fetchColumn();
$latestEbooks = $pdo->query("SELECT * FROM ebook ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Header -->
<?php include '../includes/head.php'; ?>
<!-- /Header -->

<body class="">
    <div class="page">
        <div class="page-main">
            <div class="header py-4">

                <!-- Navbar -->
                <?php include '../includes/navbar.php'; ?>
                <!-- / Navbar -->

                <div class="my-3 my-md-5">
                    <div class="container">
                        <div class="page-header">
                            <h1 class="page-title">
                                DASHBOARD
                            </h1>
                        </div>

                        <!-- Cards Invormasi-->
                        <div class="row row-cards">
                            <div class="col-6 col-sm-4 col-lg-2">
                                <div class="card">
                                    <div class="card-body p-3 text-center">
                                        <div class="h1 m-0 mt-2"><?= $totalEbooks ?></div>
                                        <div class="text-muted mb-4">Total Ebook</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4 col-lg-2">
                                <div class="card">
                                    <div class="card-body p-3 text-center">
                                        <div class="h1 m-0 mt-2"><?= $totalKategoris ?></div>
                                        <div class="text-muted mb-4">Total Kategori</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>

                        <h3 class="text-center">
                            E-Book Terbaru
                        </h3>

                        <div class="row row-cards row-deck">
                            <div class="col-12">
                                <div class="card">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-outline table-vcenter text-nowrap card-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th> <!-- Tambahkan ini -->
                                                    <th>Judul</th>
                                                    <th>Penulis</th>
                                                    <th class="text-center">Tahun</th>
                                                    <th>Tanggal Upload</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                foreach ($latestEbooks as $ebook): ?>
                                                    <tr>
                                                        <td><?= $no++ ?></td> <!-- Tambahkan ini -->
                                                        <td><?= htmlspecialchars($ebook['judul']) ?></td>
                                                        <td><?= htmlspecialchars($ebook['penulis']) ?></td>
                                                        <td class="text-center"><?= $ebook['tahun_terbit'] ?></td>
                                                        <td><?= date('d M Y', strtotime($ebook['created_at'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <div class="container">
                    <div class="row align-items-center flex-row-reverse">

                        <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-center">
                            Copyright © 2025 <a href=".">E-Book Buku Pelajaran</a>.
                        </div>
                    </div>
                </div>
            </footer>
        </div>
</body>

</html>