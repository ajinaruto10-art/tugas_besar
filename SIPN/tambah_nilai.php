<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));
$username_login = $_SESSION['username'] ?? '';

// Ambil ID user yang sedang login
$username_aman = mysqli_real_escape_string($koneksi, $username_login);

$q_user = mysqli_query(
    $koneksi,
    "SELECT id FROM users
     WHERE username = '$username_aman'
     LIMIT 1"
);

$d_user = $q_user ? mysqli_fetch_assoc($q_user) : null;
$user_id_login = $d_user['id'] ?? 0;

// Ambil seluruh data siswa
$query_siswa = mysqli_query(
    $koneksi,
    "SELECT * FROM siswa ORDER BY nama ASC"
);

// Ambil mata pelajaran
if ($role === 'guru') {
    // Menggunakan uer_id sesuai kolom di database
    $q_guru = mysqli_query(
        $koneksi,
        "SELECT id FROM guru
         WHERE uer_id = '$user_id_login'
         LIMIT 1"
    );

    $d_guru = $q_guru ? mysqli_fetch_assoc($q_guru) : null;
    $id_guru_login = $d_guru['id'] ?? 0;

    if (!empty($id_guru_login)) {
        $query_mapel = mysqli_query(
            $koneksi,
            "SELECT * FROM mapel
             WHERE guru_id = '$id_guru_login'
             ORDER BY nama_mapel ASC"
        );
    } else {
        $query_mapel = mysqli_query(
            $koneksi,
            "SELECT * FROM mapel WHERE 1=0"
        );
    }
} else {
    $query_mapel = mysqli_query(
        $koneksi,
        "SELECT * FROM mapel ORDER BY nama_mapel ASC"
    );
}

// Proses simpan nilai
if (isset($_POST['submit'])) {

    $siswa_id = mysqli_real_escape_string($koneksi, $_POST['siswa_id'] ?? '');
    $mapel_id = mysqli_real_escape_string($koneksi, $_POST['mapel_id'] ?? '');
    $nilai_tugas = (int) ($_POST['nilai_tugas'] ?? 0);
    $nilai_pengetahuan = (int) ($_POST['nilai_pengetahuan'] ?? 0);
    $nilai_keterampilan = (int) ($_POST['nilai_keterampilan'] ?? 0);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? '');

    // Validasi pilihan
    if ($siswa_id === '' || $mapel_id === '') {
        echo "<script>
            alert('Pilih siswa dan mata pelajaran terlebih dahulu!');
            window.history.back();
        </script>";
        exit;
    }

    // Validasi nilai
    if (
        $nilai_tugas < 0 || $nilai_tugas > 100 ||
        $nilai_pengetahuan < 0 || $nilai_pengetahuan > 100 ||
        $nilai_keterampilan < 0 || $nilai_keterampilan > 100
    ) {
        echo "<script>
            alert('Nilai harus antara 0 sampai 100!');
            window.history.back();
        </script>";
        exit;
    }

 // Simpan nilai ke database sesuai nama kolom asli di phpMyAdmin
    $query_insert = "
        INSERT INTO nilail_raport
        (
            siswa_id,
            mapel_id,
            nilai_tugas,
            nilial_pengetauan,
            nilail_keterampilan,
            keterangan
        )
        VALUES
        (
            '$siswa_id',
            '$mapel_id',
            '$nilai_tugas',
            '$nilai_pengetahuan',
            '$nilai_keterampilan',
            '$keterangan'
        )
    ";
    $execute = mysqli_query($koneksi, $query_insert);

    if ($execute) {
        echo "<script>
            alert('Nilai siswa berhasil disimpan!');
            window.location='tabel2.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('Gagal menyimpan nilai: " . addslashes(mysqli_error($koneksi)) . "');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Raport - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR UTAMA GURU -->
<?php if ($role === 'guru'): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="tabel2.php"><i class="bi bi-mortarboard-fill me-2"></i>SIPN - Panel Guru</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="tabel2.php"><i class="bi bi-house-door me-1"></i> Data Nilai</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="tambah_nilai.php"><i class="bi bi-plus-circle me-1"></i> Input Nilai</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tambah_mapel.php"><i class="bi bi-journal-plus me-1"></i> Tambah Mapel Saya</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<div class="container my-4" style="max-width: 700px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0 fw-bold">Input Nilai Raport Siswa</h4>
        </div>
        <div class="card-body p-4">
            <form action="" method="POST">

                <!-- PILIH SISWA -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Siswa</label>
                    <select name="siswa_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php
                        if ($query_siswa && mysqli_num_rows($query_siswa) > 0):
                            while ($siswa = mysqli_fetch_assoc($query_siswa)):
                                $id_siswa = $siswa['ID'] ?? $siswa['id'] ?? $siswa['id_siswa'] ?? '';
                                $nama_tampil = $siswa['nama_siswa'] ?? $siswa['nama'] ?? 'Siswa';
                                $nis_tampil = $siswa['nis'] ?? '-';
                        ?>
                            <option value="<?= htmlspecialchars((string) $id_siswa); ?>">
                                <?= htmlspecialchars($nama_tampil); ?> (NIS: <?= htmlspecialchars($nis_tampil); ?>)
                            </option>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </select>
                </div>

                <!-- PILIH MAPEL -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold mb-0">Mata Pelajaran yang Anda Ampu</label>
                        <?php if ($role === 'guru'): ?>
                            <a href="tambah_mapel.php" class="text-decoration-none small text-primary fw-bold">
                                <i class="bi bi-plus"></i> Tambah Mapel Baru
                            </a>
                        <?php endif; ?>
                    </div>

                    <select name="mapel_id" class="form-select" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php
                        if ($query_mapel && mysqli_num_rows($query_mapel) > 0):
                            while ($mapel = mysqli_fetch_assoc($query_mapel)):
                                // Memeriksa variasi nama kolom ID
                                $id_mapel = $mapel['ID'] ?? $mapel['id'] ?? $mapel['id_mapel'] ?? '';
                                $nama_mapel = $mapel['nama_mapel'] ?? $mapel['nama'] ?? 'Mata Pelajaran';
                                $kode_mapel = $mapel['kode_mapel'] ?? '-';
                        ?>
                            <option value="<?= htmlspecialchars((string) $id_mapel); ?>">
                                <?= htmlspecialchars($nama_mapel); ?> (Kode: <?= htmlspecialchars($kode_mapel); ?>)
                            </option>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <option value="" disabled>Belum ada mata pelajaran. Silakan klik "Tambah Mapel Baru" di atas.</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- INPUT NILAI -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Nilai Tugas</label>
                        <input type="number" name="nilai_tugas" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Nilai Pengetahuan</label>
                        <input type="number" name="nilai_pengetahuan" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Nilai Keterampilan</label>
                        <input type="number" name="nilai_keterampilan" class="form-control" min="0" max="100" required>
                    </div>
                </div>

                <!-- KETERANGAN -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Tuntas">
                </div>

                <!-- TOMBOL -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="tabel2.php" class="btn btn-secondary px-4">Kembali</a>
                    <button type="submit" name="submit" class="btn btn-primary px-4">Simpan Nilai</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>