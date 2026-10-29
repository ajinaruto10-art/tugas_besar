<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));
$username = $_SESSION['username'] ?? 'User';

// Ambil kata kunci pencarian
$keyword = mysqli_real_escape_string($koneksi, $_GET['cari'] ?? '');

$sql = "SELECT guru.*, users.username, users.role, mapel.nama_mapel 
        FROM guru 
        LEFT JOIN users ON guru.user_id = users.id 
        LEFT JOIN mapel ON mapel.id_guru = guru.ID";

if (!empty($keyword)) {
    $sql .= " WHERE users.username LIKE '%$keyword%' OR guru.nama LIKE '%$keyword%' OR mapel.nama_mapel LIKE '%$keyword%'";
}

$query = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Guru - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #fff; }
        .top-navbar {
            border-bottom: 1px solid #dee2e6;
            padding: 12px 24px;
            background-color: #fff;
        }
        .table th { 
            background-color: #f8f9fa !important; 
            color: #212529 !important; 
            font-weight: 600; 
            font-size: 0.9rem;
            border-top: 1px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
        }
        .table td { vertical-align: middle; padding: 12px 10px; }
    </style>
</head>
<body>

<!-- Top Navigation Bar -->
<div class="top-navbar d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-4">
        <span class="fw-normal text-dark">Navbar</span>
        <a href="tabel2.php" class="text-decoration-none text-dark">Home</a>
        <a href="tabel2.php" class="text-decoration-none text-dark">Nilai Siswa</a>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <!-- Kotak Pencarian di Header -->
        <form action="" method="GET" class="d-flex gap-2">
            <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" class="form-control form-control-sm" placeholder="Search" style="width: 200px;">
            <button type="submit" class="btn btn-outline-success btn-sm px-3">Search</button>
        </form>
        
        <!-- User Info / Dropdown -->
        <div class="dropdown">
            <button class="btn btn-link text-dark text-decoration-none dropdown-toggle p-0 shadow-none" type="button" data-bs-toggle="dropdown">
                <?= htmlspecialchars($username); ?> ( <?= htmlspecialchars($role); ?> )
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="pengaturan.php">Pengaturan</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</div>

<div class="container my-4">
    
    <!-- Judul Halaman -->
    <div class="text-center mb-4">
        <h2 class="fw-normal text-dark">Daftar Guru</h2>
    </div>

    <!-- Kontrol Atas Tabel (Tampilkan & Cari) -->
    <div class="row align-items-center mb-3">
        <div class="col-md-6 d-flex align-items-center">
            <span class="me-2 text-dark small">Tampilkan</span>
            <select class="form-select form-select-sm w-auto d-inline-block">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span class="ms-2 text-dark small">data per halaman</span>
        </div>
        
        <div class="col-md-6 d-flex justify-content-end align-items-center">
            <form action="" method="GET" class="d-flex align-items-center">
                <label class="me-2 text-dark small">Cari:</label>
                <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" class="form-control form-control-sm" style="width: 200px;">
            </form>
        </div>
    </div>

    <!-- Tabel Data Guru -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th width="8%">No <i class="bi bi-arrow-down-up small text-muted float-end"></i></th>
                    <th width="22%">Username <i class="bi bi-arrow-down-up small text-muted float-end"></i></th>
                    <th width="15%">Role <i class="bi bi-arrow-down-up small text-muted float-end"></i></th>
                    <th width="25%">Nama <i class="bi bi-arrow-down-up small text-muted float-end"></i></th>
                    <th width="30%">Mapel <i class="bi bi-arrow-down-up small text-muted float-end"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if ($query && mysqli_num_rows($query) > 0):
                    while ($row = mysqli_fetch_assoc($query)):
                ?>
                    <tr>
                        <td class="fw-bold"><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['role'] ?? 'guru'); ?></td>
                        <td><?= htmlspecialchars($row['nama'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['nama_mapel'] ?? '-'); ?></td>
                    </tr>
                <?php 
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted fst-italic">
                            Tidak ada data guru yang ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>