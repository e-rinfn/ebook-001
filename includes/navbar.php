<?php
// Mendapatkan nama file saat ini
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>

<!-- Navbar -->
<div class="container">
    <div class="d-flex">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="<?= $base_url ?>/assets/images/Logo.png" alt="Logo" style="height: 50px; margin-right: 10px;">
            <div class="d-flex flex-column">
                <span class="font-weight-bold text-dark" style="line-height: 1.2;">E-BOOK</span>
                <span class="font-weight-bold text-dark" style="line-height: 1.2;">PELAJARAN SEKOLAH MENENGAH PERTAMA</span>
            </div>
        </a>
    </div>
</div>

<div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3 ml-auto"></div>
            <div class="col-lg order-lg-first">
                <ul class="nav nav-tabs border-0 flex-column flex-lg-row">
                    <li class="nav-item">
                        <a href="<?= $base_url ?>/admin/dashboard.php"
                            class="nav-link <?= ($current_page == 'dashboard.php') ? 'active text-danger' : '' ?>">
                            <i class="fe fe-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base_url ?>/admin/ebook/index.php"
                            class="nav-link <?= ($current_dir == 'ebook') ? 'active text-danger' : '' ?>">
                            <i class="fe fe-book"></i> E-Book
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $base_url ?>/admin/kategori/index.php"
                            class="nav-link <?= ($current_dir == 'kategori') ? 'active text-danger' : '' ?>">
                            <i class="fe fe-loader"></i> Kategori
                        </a>
                    </li>

                    <?php if ($_SESSION['admin_role'] === 'super_admin'): ?>
                        <li class="nav-item">
                            <a href="<?= $base_url ?>/admin/users/index.php"
                                class="nav-link <?= ($current_dir == 'users') ? 'active text-danger' : '' ?>">
                                <i class="fe fe-users"></i> Pengguna
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a href="<?= $base_url ?>/admin/profile.php"
                            class="nav-link <?= ($current_page == 'profile.php') ? 'active text-danger' : '' ?>">
                            <i class="fe fe-user"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a href="<?= $base_url ?>/auth/logout.php" class="nav-link">
                            <i class="fe fe-log-out"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>