<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Panggil file koneksi database
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

// Inisialisasi variabel awal
$id_siswa_login = 0;
$user_id_login  = 0;
$kelas_siswa    = '';

// Ambil ID user dari tabel users yang sedang login
$q_user_s = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username_aman' LIMIT 1");
$d_user_s = $q_user_s ? mysqli_fetch_assoc($q_user_s) : null;
$user_id_login = $d_user_s['id'] ?? 0;

if ($role === 'siswa') {
    // Ambil data profil & kelas siswa yang sedang login
    $q_siswa = mysqli_query($koneksi, "
        SELECT * FROM siswa 
        WHERE user_id = '$user_id_login' 
           OR user_id = '$user_id_login' 
        LIMIT 1
    ");
    $d_siswa = $q_siswa ? mysqli_fetch_assoc($q_siswa) : null;

    $id_siswa_login = $d_siswa['ID'] ?? $d_siswa['id'] ?? $user_id_login;
    $nama_siswa     = $d_siswa['nama'] ?? $d_siswa['nama_siswa'] ?? $username_login;
    $kelas_siswa    = $d_siswa['kelas'] ?? 'Belum Diatur';
    $nis_siswa      = $d_siswa['nis'] ?? '-';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR UTAMA BERDASARKAN ROLE -->
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
                <li class="nav-item"><a class="nav-link active" href="tabel2.php"><i class="bi bi-house-door me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="tabel3.php"><i class="bi bi-person me-1"></i> Siswa</a></li>
                <li class="nav-item"><a class="nav-link" href="kelola_guru.php"><i class="bi bi-person-badge me-1"></i> Guru</a></li>
                <li class="nav-item"><a class="nav-link" href="mapel.php"><i class="bi bi-journal-text me-1"></i> Mapel</a></li>
                <li class="nav-item"><a class="nav-link" href="kelola_mengajar.php"><i class="bi bi-easel me-1"></i> Mengajar</a></li>
                
                <!-- DROPDOWN PENGATURAN ADMIN -->
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
                <li class="nav-item"><a class="nav-link active" href="tabel2.php"><i class="bi bi-house-door me-1"></i> Data Nilai</a></li>
                <li class="nav-item"><a class="nav-link" href="tambah_nilai.php"><i class="bi bi-plus-circle me-1"></i> Input Nilai</a></li>
                <li class="nav-item"><a class="nav-link" href="tambah_mapel.php"><i class="bi bi-journal-plus me-1"></i> Tambah Mapel Saya</a></li>
                <li class="nav-item"><a class="nav-link" href="daftar_guru.php"><i class="bi bi-people me-1"></i> Daftar Guru</a></li>
                
                <!-- DROPDOWN PENGATURAN GURU -->
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
                <li class="nav-item"><a class="nav-link active" href="tabel2.php"><i class="bi bi-journal-text me-1"></i> Nilai & Kelas Saya</a></li>
                
                <!-- DROPDOWN PENGATURAN SISWA -->
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

<div class="container my-4">

    <?php if ($role === 'siswa'): ?>
        <!-- KARTU INFORMASI PROFIL SISWA -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Informasi Siswa</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <small class="text-muted d-block">Nama Lengkap</small>
                        <span class="fs-5 fw-bold text-dark"><?= htmlspecialchars($nama_siswa); ?></span>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <small class="text-muted d-block">Kelas</small>
                        <span class="badge bg-success fs-6"><i class="bi bi-building me-1"></i><?= htmlspecialchars($kelas_siswa); ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">NIS / Username</small>
                        <span class="fw-bold text-secondary"><?= htmlspecialchars($nis_siswa !== '-' ? $nis_siswa : $username_login); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL DAFTAR SISWA SE-KELAS -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-success"><i class="bi bi-people-fill me-2"></i>Daftar Siswa Kelas <?= htmlspecialchars($kelas_siswa); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-success">
                            <tr>
                                <th width="10%">No</th>
                                <th>Nama Siswa</th>
                                <th width="30%">Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $kelas_aman = mysqli_real_escape_string($koneksi, $kelas_siswa);
                            $query_teman = mysqli_query($koneksi, "
                                SELECT * FROM siswa 
                                WHERE kelas = '$kelas_aman' 
                                ORDER BY nama ASC
                            ");
                            
                            $no_t = 1;
                            if ($query_teman && mysqli_num_rows($query_teman) > 0):
                                while ($t_row = mysqli_fetch_assoc($query_teman)):
                            ?>
                                <tr>
                                    <td><?= $no_t++; ?></td>
                                    <td><?= htmlspecialchars($t_row['nama'] ?? $t_row['nama_siswa'] ?? '-'); ?></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($t_row['kelas'] ?? $kelas_siswa); ?></span></td>
                                </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                               <tr>
                                   <td colspan="3" class="text-center text-muted py-3">Tidak ada data siswa lain di kelas ini.</td>
                               </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- TABEL DATA NILAI -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold <?= $role === 'admin' ? 'text-dark' : ($role === 'guru' ? 'text-primary' : 'text-success'); ?>">
                <?= $role === 'admin' ? 'Daftar Nilai Raport Siswa' : ($role === 'guru' ? 'Daftar Nilai Raport Siswa' : 'Daftar Nilai Raport Saya (Privat)'); ?>
            </h4>
            <?php if ($role === 'guru' || $role === 'admin'): ?>
                <a href="tambah_nilai.php" class="btn <?= $role === 'admin' ? 'btn-dark' : 'btn-primary'; ?> btn-sm fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i> Input Nilai Baru
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="<?= $role === 'admin' ? 'table-dark' : ($role === 'guru' ? 'table-primary' : 'table-success'); ?>">
                        <tr>
                            <th>No</th>
                            <?php if ($role === 'guru' || $role === 'admin'): ?>
                                <th>Nama Siswa</th>
                            <?php endif; ?>
                            <th>Mata Pelajaran</th>
                            <th>Nilai Tugas</th>
                            <th>Pengetahuan</th>
                            <th>Keterampilan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($role === 'admin') {
                            // Admin bisa melihat seluruh data nilai
                            $query_tampil = mysqli_query($koneksi, "
                                SELECT nr.*, s.nama AS nama_siswa, m.nama_mapel 
                                FROM nilail_raport nr 
                                LEFT JOIN siswa s ON nr.siswa_id = s.ID OR nr.siswa_id = s.id 
                                LEFT JOIN mapel m ON nr.mapel_id = m.ID OR nr.mapel_id = m.id 
                                ORDER BY nr.ID DESC
                            ");
                        } elseif ($role === 'guru') {
                            // Guru melihat nilai siswa berdasarkan mapel yang diampunya
                            $q_g = mysqli_query($koneksi, "SELECT id FROM guru WHERE uer_id = '$user_id_login' LIMIT 1");
                            $d_g = $q_g ? mysqli_fetch_assoc($q_g) : null;
                            $g_id = $d_g['id'] ?? 0;

                            if (!empty($g_id)) {
                                $query_tampil = mysqli_query($koneksi, "
                                    SELECT nr.*, s.nama AS nama_siswa, m.nama_mapel 
                                    FROM nilail_raport nr 
                                    LEFT JOIN siswa s ON nr.siswa_id = s.ID OR nr.siswa_id = s.id 
                                    LEFT JOIN mapel m ON nr.mapel_id = m.ID OR nr.mapel_id = m.id 
                                    WHERE m.guru_id = '$g_id'
                                    ORDER BY nr.ID DESC
                                ");
                            } else {
                                $query_tampil = mysqli_query($koneksi, "
                                    SELECT nr.*, s.nama AS nama_siswa, m.nama_mapel 
                                    FROM nilail_raport nr 
                                    LEFT JOIN siswa s ON nr.siswa_id = s.ID OR nr.siswa_id = s.id 
                                    LEFT JOIN mapel m ON nr.mapel_id = m.ID OR nr.mapel_id = m.id 
                                    ORDER BY nr.ID DESC
                                ");
                            }
                        } else {
                            // SISWA: HANYA MENAMPILKAN NILAI MILIK DIRINYA SENDIRI
                            $query_tampil = mysqli_query($koneksi, "
                                SELECT nr.*, m.nama_mapel 
                                FROM nilail_raport nr 
                                JOIN mapel m ON nr.mapel_id = m.ID OR nr.mapel_id = m.id 
                                WHERE nr.siswa_id = '$id_siswa_login' OR nr.siswa_id = '$user_id_login'
                                ORDER BY nr.ID DESC
                            ");
                        }

                        $no = 1;
                        if ($query_tampil && mysqli_num_rows($query_tampil) > 0):
                            while ($row = mysqli_fetch_assoc($query_tampil)):
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <?php if ($role === 'guru' || $role === 'admin'): ?>
                                    <td><?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($row['nama_mapel'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($row['nilai_tugas'] ?? '0'); ?></td>
                                <td><?= htmlspecialchars($row['nilail_pengetahuan'] ?? '0'); ?></td>
                                <td><?= htmlspecialchars($row['nilail_keterampilan'] ?? '0'); ?></td>
                                <td><?= htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="<?= ($role === 'guru' || $role === 'admin') ? '7' : '6'; ?>" class="text-center text-muted py-4">
                                    Belum ada data nilai raport yang tersimpan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>