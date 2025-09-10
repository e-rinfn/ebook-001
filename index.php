<?php
require_once 'config/database.php';

// Ambil parameter pencarian dan filter
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilters = isset($_GET['categories']) ? $_GET['categories'] : [];
$categoryFilters = array_filter(array_map('intval', $categoryFilters)); // Filter nilai kosong/non-numeric

$db = getDBConnection();
$latestBooks = [];
$categories = [];
$filteredBooks = [];

try {
  // Query untuk mendapatkan semua kategori
  $stmt = $db->query("SELECT * FROM kategori ORDER BY nama");
  $categories = $stmt->fetchAll();

  // Query untuk mendapatkan buku dengan filter dan pencarian
  $query = "SELECT DISTINCT ebook.* FROM ebook";
  $params = [];
  $whereConditions = [];

  // Tambahkan kondisi pencarian
  if (!empty($searchQuery)) {
    $whereConditions[] = "(judul LIKE ? OR penulis LIKE ? OR deskripsi LIKE ?)";
    $searchTerm = "%$searchQuery%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
  }

  // Tambahkan filter kategori jika ada
  if (!empty($categoryFilters)) {
    $query .= " JOIN ebook_kategori ON ebook.id = ebook_kategori.ebook_id";
    $placeholders = implode(',', array_fill(0, count($categoryFilters), '?'));
    $whereConditions[] = "ebook_kategori.kategori_id IN ($placeholders)";
    $params = array_merge($params, $categoryFilters);
  }

  // Gabungkan semua kondisi WHERE
  if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(' AND ', $whereConditions);
  }

  $query .= " ORDER BY created_at DESC";

  $stmt = $db->prepare($query);
  $stmt->execute($params);
  $filteredBooks = $stmt->fetchAll();

  // Tetap ambil 6 buku terbaru
  $stmt = $db->query("SELECT * FROM ebook ORDER BY created_at DESC");
  $latestBooks = $stmt->fetchAll();
} catch (PDOException $e) {
  $error = "Gagal memuat data: " . $e->getMessage();
}
?>

<!-- Header -->
<?php include './includes/head.php'; ?>
<!-- /Header -->

<body class="">
  <div class="page">
    <div class="page-main">
      <div class="header py-4">

        <!-- Navbar -->
        <?php include './includes/navbar-user.php'; ?>
        <!-- / Navbar -->

        <div class="my-3 my-md-5">
          <div class="container">
            <div class="page-header text-center mb-4">
              <h1 class="page-title fw-bold">
                KOLEKSI E-BOOK PELAJARAN SEKOLAH MENENGAH PERTAMA
              </h1>
            </div>

            <div class="card shadow-sm bg-light border-0 mb-4">
              <div class="container">
                <div class="row align-items-center">

                  <!-- Gambar sebelah kiri -->
                  <div class="text-center col-md-6 mb-4 mb-md-0 p-3">
                    <img src="assets/images/Gambar-Buku.png" width="350px" class="img-fluid rounded shadow-sm" alt="Koleksi E-Book SMP">
                  </div>

                  <!-- Deskripsi sebelah kanan -->
                  <div class="col-md-6">
                    <h2 class="text-center fw-bold mb-3">Koleksi E-Book SMP</h2>
                    <p class="text-muted text-justify p-3">
                      Kumpulan e-book pelajaran <strong>Sekolah Menengah Pertama (SMP)</strong>
                      yang disusun berdasarkan kurikulum terbaru. Materi dapat diakses secara
                      mudah sebagai referensi belajar tambahan untuk membantu siswa memahami
                      pelajaran, mengerjakan tugas, serta mempersiapkan ujian dengan lebih baik.
                    </p>
                  </div>

                </div>
              </div>
            </div>


            <!-- Form Pencarian dan Tombol Trigger Modal -->
            <div class="card mb-4">
              <div class="card-body">
                <form method="get" action="">


                  <div class="col-md-4 m-0">
                    <!-- Tombol untuk membuka modal filter -->
                    <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#filterModal">
                      <i class="fe fe-filter mr-2"></i>Filter dan Cari Buku
                    </button>
                    <?php if (!empty($searchQuery) || !empty($categoryFilters)): ?>
                      <a href="?" class="btn btn-outline-secondary ml-2">Reset</a>
                    <?php endif; ?>
                  </div>


                  <!-- Modal Filter -->
                  <div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="filterModalLabel">Filter Buku</h5>
                          <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">

                          </button> -->
                        </div>
                        <div class="modal-body">
                          <div class="container-fluid">
                            <div class="row">
                              <div class="col-12 mb-4">
                                <label for="searchModal" class="form-label">Cari Buku</label>
                                <input type="text" class="form-control" id="searchModal" name="search"
                                  placeholder="Judul, penulis, atau deskripsi..." value="<?= htmlspecialchars($searchQuery) ?>">
                              </div>

                              <div class="col-12">
                                <label class="form-label d-block mb-3">Filter Kategori</label>
                                <div class="row">
                                  <?php foreach ($categories as $category): ?>
                                    <div class="col-6 col-md-4 col-lg-3 mb-3">
                                      <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                          id="category-<?= $category['id'] ?>"
                                          name="categories[]"
                                          value="<?= $category['id'] ?>"
                                          <?= in_array($category['id'], $categoryFilters) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="category-<?= $category['id'] ?>">
                                          <?= htmlspecialchars($category['nama']) ?>
                                        </label>
                                      </div>
                                    </div>
                                  <?php endforeach; ?>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
                          <button type="submit" class="btn btn-danger">Terapkan Filter</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Hasil Pencarian -->
            <?php if (!empty($searchQuery) || !empty($categoryFilters)): ?>
              <div class="mb-4">
                <h3>Hasil Pencarian</h3>
                <?php if (empty($filteredBooks)): ?>
                  <div class="alert alert-info">
                    Tidak ditemukan buku yang sesuai dengan kriteria pencarian.
                  </div>
                <?php else: ?>
                  <div class="row">
                    <?php foreach ($filteredBooks as $book): ?>
                      <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 d-flex flex-column">
                          <?php if ($book['cover_url']): ?>
                            <img src="uploads/covers/<?= htmlspecialchars($book['cover_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($book['judul']); ?>">
                          <?php else: ?>
                            <img src="https://via.placeholder.com/150x200?text=No+Cover" class="card-img-top" alt="Cover tidak tersedia">
                          <?php endif; ?>

                          <div class="card-body d-flex flex-column">
                            <h5 class="card-title font-weight-bold"><?= htmlspecialchars($book['judul']); ?></h5>

                            <p class="card-text text-justify" id="desc-<?= $book['id']; ?>">
                              <?= nl2br(htmlspecialchars(mb_strimwidth($book['deskripsi'], 0, 150, '...'))) ?>
                              <a href="#" onclick="toggleDescription(<?= $book['id']; ?>); return false;"><br>Lihat Selengkapnya</a>
                            </p>
                            <p class="card-text text-justify d-none" id="desc-full-<?= $book['id']; ?>">
                              <?= nl2br(htmlspecialchars($book['deskripsi'])) ?>
                              <a href="#" onclick="toggleDescription(<?= $book['id']; ?>, false); return false;"><br>Tutup</a>
                            </p>

                            <p class="card-text mb-1">Oleh: <?= htmlspecialchars($book['penulis']); ?></p>
                            <p class="card-text mb-3"><small class="text-muted">Tahun: <?= htmlspecialchars($book['tahun_terbit']); ?></small></p>

                            <div class="mt-auto">
                              <a href="uploads/ebooks/<?= htmlspecialchars($book['file_url']); ?>" target="_blank" class="btn btn-sm btn-danger btn-block">Baca E-Book</a>
                            </div>
                          </div>

                        </div>
                      </div>



                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <!-- E-Book Terbaru -->
            <h3 class="mt-4">
              E-Book Terbaru
            </h3>

            <div class="row">
              <?php foreach ($latestBooks as $book): ?>
                <div class="col-6 col-md-4 col-lg-3 mb-4">
                  <div class="card h-100">
                    <?php if ($book['cover_url']): ?>
                      <img src="uploads/covers/<?= htmlspecialchars($book['cover_url']); ?>" class="card-img-top" alt="<?= htmlspecialchars($book['judul']); ?>">
                    <?php else: ?>
                      <img src="https://via.placeholder.com/150x200?text=No+Cover" class="card-img-top" alt="Cover tidak tersedia">
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                      <h5 class="card-title font-weight-bold"><?= htmlspecialchars($book['judul']); ?></h5>

                      <p class="card-text text-justify" id="desc-<?= $book['id']; ?>">
                        <?= nl2br(htmlspecialchars(mb_strimwidth($book['deskripsi'], 0, 150, '...'))) ?>
                        <a href="#" onclick="toggleDescription(<?= $book['id']; ?>); return false;"><br>Lihat Selengkapnya</a>
                      </p>
                      <p class="card-text text-justify d-none" id="desc-full-<?= $book['id']; ?>">
                        <?= nl2br(htmlspecialchars($book['deskripsi'])) ?>
                        <a href="#" onclick="toggleDescription(<?= $book['id']; ?>, false); return false;"><br>Tutup</a>
                      </p>

                      <p class="card-text mb-1">Oleh: <?= htmlspecialchars($book['penulis']); ?></p>
                      <p class="card-text mb-3"><small class="text-muted">Tahun: <?= htmlspecialchars($book['tahun_terbit']); ?></small></p>

                      <div class="mt-auto">
                        <a href="uploads/ebooks/<?= htmlspecialchars($book['file_url']); ?>" target="_blank" class="btn btn-sm btn-danger btn-block">Baca E-Book</a>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>


            <script>
              function toggleDescription(id, expand = true) {
                const shortDesc = document.getElementById(`desc-${id}`);
                const fullDesc = document.getElementById(`desc-full-${id}`);

                if (expand) {
                  shortDesc.classList.add('d-none');
                  fullDesc.classList.remove('d-none');
                } else {
                  shortDesc.classList.remove('d-none');
                  fullDesc.classList.add('d-none');
                }
              }
            </script>


            <!-- Daftar Kategori -->
            <h2 class="mt-5 mb-3">Kategori Buku</h2>
            <div class="d-flex flex-wrap m-3">
              <?php foreach ($categories as $category): ?>
                <a href="?categories[]=<?= $category['id'] ?>" class="badge bg-secondary m-2 text-decoration-none">
                  <?= htmlspecialchars($category['nama']); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <footer class="footer">
        <div class="container">
          <div class="row align-items-center flex-row-reverse">
            <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-center">
              Copyright © 2025 erinfn <br> <a href=".">E-Book Pelajaran Sekolah Menengah Pertama</a>.
            </div>
          </div>
        </div>
      </footer>
    </div>
</body>

</html>