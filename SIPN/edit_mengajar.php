<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

$id_param       = trim($_GET['id'] ?? '');
$mapel_id_param = trim($_GET['mapel_id'] ?? '');

if ($id_param === '' && $mapel_id_param === '') {
    echo "<script>alert('ID data mengajar tidak ditemukan!'); window.location.href='daftar_mengajar.php';</script>";
    exit;
}

$data_mapel = null;
if ($mapel_id_param !== '') {
    $m_esc = mysqli_real_escape_string($koneksi, $mapel_id_param);
    $q = mysqli_query($koneksi, "SELECT * FROM mapel WHERE ID = '$m_esc' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $data_mapel = mysqli_fetch_assoc($q);
    }
}

if (!$data_mapel && $id_param !== '') {
    $id_esc = mysqli_real_escape_string($koneksi, $id_param);
    $q_mg = mysqli_query($koneksi, "SELECT mapel_id FROM mengajar WHERE id = '$id_esc' LIMIT 1");
    if ($q_mg && $rmg = mysqli_fetch_assoc($q_mg)) {
        $mid = mysqli_real_escape_string($koneksi, (string)($rmg['mapel_id'] ?? ''));
        if ($mid !== '') {
            $q_m = mysqli_query($koneksi, "SELECT * FROM mapel WHERE ID = '$mid' LIMIT 1");
            if ($q_m && mysqli_num_rows($q_m) > 0) {
                $data_mapel = mysqli_fetch_assoc($q_m);
            }
        }
    }
}

if (!$data_mapel && $id_param !== '') {
    $id_esc = mysqli_real_escape_string($koneksi, $id_param);
    $q_m2 = mysqli_query($koneksi, "SELECT * FROM mapel WHERE ID = '$id_esc' LIMIT 1");
    if ($q_m2 && mysqli_num_rows($q_m2) > 0) {
        $data_mapel = mysqli_fetch_assoc($q_m2);
    }
}

if (!$data_mapel) {
    echo "<script>alert('Data mengajar tidak ditemukan!'); window.location.href='daftar_mengajar.php';</script>";
    exit;
}

$mapel_id_aktif = (string)($data_mapel['ID'] ?? '');
$kelas_aktif    = trim((string)($data_mapel['kelas'] ?? ''));
$guru_id_aktif  = trim((string)($data_mapel['id_guru'] ?? ''));

$daftar_kelas = [];
$res_siswa_kelas = mysqli_query($koneksi, "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC");
if ($res_siswa_kelas) {
    while ($rs = mysqli_fetch_assoc($res_siswa_kelas)) {
        $nama_k = trim((string)$rs['kelas']);
        if ($nama_k !== '' && !in_array($nama_k, $daftar_kelas, true)) {
            $daftar_kelas[] = $nama_k;
        }
    }
}
if ($kelas_aktif !== '' && !in_array($kelas_aktif, $daftar_kelas, true)) {
    array_unshift($daftar_kelas, $kelas_aktif);
}

$query_guru = mysqli_query($koneksi, "SELECT * FROM guru ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mengajar - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>

<div class="container my-4">
    <div class="card shadow-sm border">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Form Edit Mengajar</h5>
        </div>
        <div class="card-body p-4">
            <h3 class="fw-bold mb-4">Edit Mengajar</h3>
            <form action="aksi_edit_mengajar.php" method="POST">
                <input type="hidden" name="mapel_id" value="<?= htmlspecialchars($mapel_id_aktif); ?>">
                <input type="hidden" name="id_asal" value="<?= htmlspecialchars($id_param); ?>">

                <div class="mb-3">
                    <label for="kelas" class="form-label">Pilih Kelas</label>
                    <select class="form-select" id="kelas" name="kelas" required>
                        <option value="">Pilih Kelas...</option>
                        <?php foreach ($daftar_kelas as $kls): ?>
                            <option value="<?= htmlspecialchars($kls); ?>" <?= ($kls === $kelas_aktif) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kls); ?>
                            </option>
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
                                $id_g   = (string)($g['id'] ?? ($g['ID'] ?? ''));
                                $nama_g = (string)($g['nama'] ?? ($g['nama_guru'] ?? ''));
                                ?>
                                <option value="<?= htmlspecialchars($id_g); ?>" <?= ($id_g === $guru_id_aktif) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($nama_g); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="update_mengajar" class="btn btn-primary fw-semibold px-3">Simpan Perubahan</button>
                    <a href="daftar_mengajar.php" class="btn btn-secondary fw-semibold px-3">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>