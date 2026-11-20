<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role           = strtolower(trim($_SESSION['role'] ?? ''));
$username_login = $_SESSION['username'] ?? '';
$username_aman  = mysqli_real_escape_string($koneksi, $username_login);

// Ambil parameter kelas dan mapel dari URL
$kelas_dipilih = isset($_GET['kelas']) ? mysqli_real_escape_string($koneksi, $_GET['kelas']) : '';
$nama_mapel    = isset($_GET['mapel']) ? mysqli_real_escape_string($koneksi, $_GET['mapel']) : '';

// Ambil data user/guru yang sedang login (menggunakan user_id)
$q_user = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username_aman' LIMIT 1");
$d_user = $q_user ? mysqli_fetch_assoc($q_user) : null;
$user_id = $d_user['id'] ?? 0;

$q_guru = mysqli_query($koneksi, "SELECT * FROM guru WHERE user_id = '$user_id' OR id = '$user_id' LIMIT 1");
$d_guru = $q_guru ? mysqli_fetch_assoc($q_guru) : null;
$nama_guru = $d_guru['nama'] ?? $username_login;

// Query data siswa dan nilai berdasarkan tabel nilai
$query_nilai = mysqli_query($koneksi, "
    SELECT s.id AS siswa_id, s.nama AS nama_siswa, n.id AS nilai_id, 
           n.nilai_pengetahuan, n.nilai_keterampilan 
    FROM siswa s 
    LEFT JOIN nilai n ON s.nama = n.nama_siswa AND n.nama_mapel = '$nama_mapel'
    WHERE s.kelas = '$kelas_dipilih'
    ORDER BY s.nama ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Nilai Siswa - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR GURU (BIRU SERAGAM) -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-semibold text-white" href="daftar_guru.php">SIPN Guru</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link text-white" href="daftar_guru.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active fw-bold text-white" href="daftar_kelas_guru.php">Nilai Siswa</a></li>
            </ul>
            
            <form class="d-flex mx-auto" role="search" style="width: 300px;">
                <input class="form-control form-control-sm me-2" type="search" placeholder="Search" aria-label="Search">
                <button class="btn btn-outline-light btn-sm" type="submit">Search</button>
            </form>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="navbar-brand dropdown-toggle text-white fs-6" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= htmlspecialchars($nama_guru); ?> (<?= htmlspecialchars($role); ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="profile.php">Profil Saya</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-semibold" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- KONTEN UTAMA -->
<div class="container my-4">
    <div class="mb-3">
        <a href="daftar_kelas_guru.php" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kelas</a>
        <h2 class="fw-bold">Daftar Nilai Siswa</h2>
        <p class="text-muted">Kelas: <span class="fw-semibold text-dark"><?= htmlspecialchars($kelas_dipilih); ?></span> | Mata Pelajaran: <span class="fw-semibold text-dark"><?= htmlspecialchars($nama_mapel); ?></span></p>
    </div>

    <div class="card border-0 shadow-sm p-3 bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="table-light border-bottom">
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th style="width: 30%;">Nama Siswa</th>
                        <th style="width: 15%;">Pengetahuan</th>
                        <th style="width: 15%;">Keterampilan</th>
                        <th style="width: 22%;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if ($query_nilai && mysqli_num_rows($query_nilai) > 0):
                        while ($row = mysqli_fetch_assoc($query_nilai)):
                            $pengetahuan  = $row['nilai_pengetahuan'];
                            $keterampilan = $row['nilai_keterampilan'];
                            $ada_nilai    = !is_null($row['nilai_id']);
                    ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td class="fw-semibold"><?= htmlspecialchars($row['nama_siswa']); ?></td>
                        <td>
                            <?php if ($ada_nilai && $pengetahuan !== null): ?>
                                <span class="badge bg-primary px-3 py-2"><?= htmlspecialchars($pengetahuan); ?></span>
                            <?php else: ?>
                                <span class="text-muted small">Belum ada</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ada_nilai && $keterampilan !== null): ?>
                                <span class="badge bg-primary px-3 py-2"><?= htmlspecialchars($keterampilan); ?></span>
                            <?php else: ?>
                                <span class="text-muted small">Belum ada</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($ada_nilai): ?>
                                <a href="edit_nilai.php?id=<?= $row['nilai_id']; ?>" class="btn btn-warning btn-sm text-white px-2 py-1">Edit</a>
                                <a href="hapus_nilai.php?id=<?= $row['nilai_id']; ?>" class="btn btn-danger btn-sm px-2 py-1" onclick="return confirm('Yakin ingin menghapus nilai ini?')">Hapus</a>
                            <?php else: ?>
                                <a href="tambah_nilai.php?nama=<?= urlencode($row['nama_siswa']); ?>&mapel=<?= urlencode($nama_mapel); ?>" class="btn btn-primary btn-sm px-3 py-1">Tambah</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data siswa di kelas ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>