<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek status login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role           = strtolower(trim($_SESSION['role'] ?? ''));
$username_login = $_SESSION['username'] ?? '';
$username_aman  = mysqli_real_escape_string($koneksi, $username_login);

// Ambil data guru dari database
$sql = "SELECT g.*, u.username 
        FROM guru g 
        LEFT JOIN users u ON uer_id = u.id OR uer_id = u.id 
        ORDER BY g.id DESC";

$hasil = mysqli_query($koneksi, $sql);

$data_guru = []; 
if ($hasil && mysqli_num_rows($hasil) > 0) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        $data_guru[] = $row;
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Guru - SIPN</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">

<!-- NAVBAR DINAMIS BERDASARKAN ROLE YANG LOGIN -->
<?php if ($role === 'admin'): ?>
<!-- NAVBAR ADMIN -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="tabel2.php"><i class="bi bi-mortarboard-fill me-2"></i>SIPN - Panel Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="tabel2.php"><i class="bi bi-house-door me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="kelola_user.php"><i class="bi bi-people me-1"></i> User</a></li>
                <li class="nav-item"><a class="nav-link" href="tabel3.php"><i class="bi bi-person me-1"></i> Siswa</a></li>
                <li class="nav-item"><a class="nav-link active" href="tabel_guru.php"><i class="bi bi-person-badge me-1"></i> Guru</a></li>
                <li class="nav-item"><a class="nav-link" href="mapel.php"><i class="bi bi-journal-text me-1"></i> Mapel</a></li>
                <li class="nav-item"><a class="nav-link" href="kelola_mengajar.php"><i class="bi bi-easel me-1"></i> Mengajar</a></li>
                
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle btn btn-outline-light text-white px-3 py-1 mt-1 mt-lg-0" href="#" id="navbarDropdownAdmin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear-fill me-1"></i> Pengaturan
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdownAdmin">
                        <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person-circle me-2"></i> Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php elseif ($role === 'guru'): ?>
<!-- NAVBAR GURU -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="tabel2.php"><i class="bi bi-mortarboard-fill me-2"></i>SIPN - Panel Guru</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="tabel2.php"><i class="bi bi-house-door me-1"></i> Data Nilai</a></li>
                <li class="nav-item"><a class="nav-link" href="tambah_nilai.php"><i class="bi bi-plus-circle me-1"></i> Input Nilai</a></li>
                <li class="nav-item"><a class="nav-link" href="tambah_mapel.php"><i class="bi bi-journal-plus me-1"></i> Tambah Mapel Saya</a></li>
                <li class="nav-item"><a class="nav-link active" href="tabel_guru.php"><i class="bi bi-people me-1"></i> Daftar Guru</a></li>
                
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle btn btn-outline-light text-white px-3 py-1 mt-1 mt-lg-0" href="#" id="navbarDropdownGuru" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear-fill me-1"></i> Pengaturan
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdownGuru">
                        <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person-circle me-2 text-primary"></i> Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php else: ?>
<!-- NAVBAR SISWA -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="tabel2.php"><i class="bi bi-mortarboard-fill me-2"></i>SIPN - Panel Siswa</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="tabel2.php"><i class="bi bi-journal-text me-1"></i> Nilai & Kelas Saya</a></li>
                
                <li class="nav-item dropdown ms-lg-2">
                    <a class="nav-link dropdown-toggle btn btn-outline-light text-white px-3 py-1 mt-1 mt-lg-0" href="#" id="navbarDropdownSiswa" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear-fill me-1"></i> Pengaturan
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdownSiswa">
                        <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person-circle me-2 text-success"></i> Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- KONTEN UTAMA HALAMAN -->
<div class="container mt-4 mb-5">
    <div class="container center mb-3">
        <h1 class="fw-normal display-5">Daftar Guru Pengajar</h1>
        <hr class="border border-dark border-2 opacity-100">
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th scope="col" width="8%">No</th>
              <th scope="col">Nama Guru</th>
              <th scope="col">NIP / Kode</th>
              <th scope="col">Username Akun</th>
              <?php if ($role === 'admin'): ?>
                  <th scope="col" style="width: 15%;" class="text-center">Actions</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1; 
            if (!empty($data_guru)) {
                foreach ($data_guru as $g) {
                    $nama_guru = $g['nama'] ?? $g['nama_guru'] ?? '-';
                    $nip_guru  = $g['nip'] ?? $g['kode_ guru'] ?? $g['id'] ?? '-';
            ?>
              <tr>
                <th scope="row"><?php echo $no++; ?></th>
                <td><strong><?php echo htmlspecialchars($nama_guru); ?></strong></td>
                <td><?php echo htmlspecialchars($nip_guru); ?></td>
                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($g['username'] ?? '-'); ?></span></td>
                
                <?php if ($role === 'admin'): ?>
                    <td class="text-center">
                      <a href="edit_guru.php?id=<?php echo $g['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                      <a href="hapus_guru.php?id=<?php echo $g['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data guru ini?')">Delete</a>
                    </td>
                <?php endif; ?>
              </tr>
            <?php 
                } 
            } else {
                $colspan = ($role === 'admin') ? '5' : '4';
                echo "<tr><td colspan='$colspan' class='text-center text-muted py-4'>Belum ada data guru yang tersedia.</td></tr>";
            }
            ?>
          </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>