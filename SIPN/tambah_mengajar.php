<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek hak akses admin
if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil daftar kelas unik dari tabel siswa (atau tabel kelas jika tersedia)
$daftar_kelas = [];
$cek_tbl_kelas = mysqli_query($koneksi, "SHOW TABLES LIKE 'kelas'");
if ($cek_tbl_kelas && mysqli_num_rows($cek_tbl_kelas) > 0) {
    $res_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY 1 ASC");
    if ($res_kelas) {
        while ($rk = mysqli_fetch_assoc($res_kelas)) {
            $nama_k = trim($rk['kelas'] ?? ($rk['nama_kelas'] ?? ''));
            if ($nama_k !== '' && !in_array($nama_k, $daftar_kelas)) {
                $daftar_kelas[] = $nama_k;
            }
        }
    }
}

if (empty($daftar_kelas)) {
    $res_siswa_kelas = mysqli_query($koneksi, "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC");
    if ($res_siswa_kelas) {
        while ($rs = mysqli_fetch_assoc($res_siswa_kelas)) {
            $nama_k = trim($rs['kelas']);
            if ($nama_k !== '') {
                $daftar_kelas[] = $nama_k;
            }
        }
    }
}

// Ambil daftar guru dari tabel guru
$query_guru = mysqli_query($koneksi, "SELECT * FROM guru ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mengajar - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-4">
    <div class="card shadow-sm border">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Form Tambah Mengajar</h5>
        </div>
        <div class="card-body p-4">
            <h3 class="fw-bold mb-4">Tambah Mengajar</h3>

            <form action="aksi_tambah_mengajar.php" method="POST">
                <div class="mb-3">
                    <label for="kelas" class="form-label">Pilih Kelas</label>
                    <select class="form-select" id="kelas" name="kelas" required>
                        <option value="">Pilih Kelas...</option>
                        <?php foreach ($daftar_kelas as $kls): ?>
                            <option value="<?= htmlspecialchars($kls); ?>"><?= htmlspecialchars($kls); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="guru_id" class="form-label">Pilih Guru</label>
                    <select class="form-select" id="guru_id" name="guru_id" required>
                        <option value="">Pilih Guru...</option>
                        <?php if ($query_guru && mysqli_num_rows($query_guru) > 0): ?>
                            <?php while ($g = mysqli_fetch_assoc($query_guru)): ?>
                                <?php
                                $id_guru   = $g['id'] ?? ($g['ID'] ?? '');
                                $nama_guru = $g['nama'] ?? ($g['nama_guru'] ?? '');
                                $mapel_g   = $g['mapel'] ?? '';
                                ?>
                                <option value="<?= htmlspecialchars($id_guru); ?>">
                                    <?= htmlspecialchars($nama_guru); ?><?= !empty($mapel_g) ? ' (' . htmlspecialchars($mapel_g) . ')' : ''; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="simpan_mengajar" class="btn btn-primary fw-semibold px-3">Simpan Data</button>
                    <a href="daftar_mengajar.php" class="btn btn-secondary fw-semibold px-3">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>