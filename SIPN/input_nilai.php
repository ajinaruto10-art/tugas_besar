<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

$role_sesi = strtolower(trim((string)($_SESSION['role'] ?? '')));
if (empty($_SESSION['login']) || !in_array($role_sesi, ['admin', 'guru'], true)) {
    header("Location: login.php");
    exit;
}

$mapel_id_get  = trim((string)($_GET['mapel_id'] ?? ''));
$kelas_get     = trim((string)($_GET['kelas'] ?? ''));
$user_id_login = trim((string)($_SESSION['user_id'] ?? ($_SESSION['id'] ?? '')));

function ambil_kolom_in(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kolom_mapel = ambil_kolom_in($koneksi, 'mapel');
$kolom_siswa = ambil_kolom_in($koneksi, 'siswa');
$kolom_guru  = ambil_kolom_in($koneksi, 'guru');

$pk_mapel = in_array('ID', $kolom_mapel, true) ? 'ID' : (in_array('id', $kolom_mapel, true) ? 'id' : 'ID');
$pk_siswa = in_array('ID', $kolom_siswa, true) ? 'ID' : (in_array('id', $kolom_siswa, true) ? 'id' : 'ID');
$kol_kelas_siswa = in_array('kelas', $kolom_siswa, true) ? 'kelas' : (in_array('nama_kelas', $kolom_siswa, true) ? 'nama_kelas' : '');

// Ambil info mapel otomatis dari mapel_id
$info_mapel = null;
if ($mapel_id_get !== '' && !empty($kolom_mapel)) {
    $mid_esc = mysqli_real_escape_string($koneksi, $mapel_id_get);
    $q_m = mysqli_query($koneksi, "SELECT * FROM mapel WHERE `$pk_mapel` = '$mid_esc' LIMIT 1");
    if ($q_m && mysqli_num_rows($q_m) > 0) {
        $info_mapel = mysqli_fetch_assoc($q_m);
        if ($kelas_get === '' && isset($info_mapel['kelas']) && trim((string)$info_mapel['kelas']) !== '') {
            $kelas_get = trim((string)$info_mapel['kelas']);
        }
    }
}

$nama_mapel_terpilih = '';
if ($info_mapel) {
    $nama_mapel_terpilih = (string)($info_mapel['nama_mapel'] ?? ($info_mapel['mapel'] ?? ''));
}

// Fallback ambil mapel dari profil guru jika belum dapat
if ($nama_mapel_terpilih === '' && $user_id_login !== '' && !empty($kolom_guru)) {
    $uid_esc = mysqli_real_escape_string($koneksi, $user_id_login);
    $where_g = [];
    if (in_array('user_id', $kolom_guru, true)) {
        $where_g[] = "user_id = '$uid_esc'";
    }
    if (in_array('id_user', $kolom_guru, true)) {
        $where_g[] = "id_user = '$uid_esc'";
    }
    if (!empty($where_g)) {
        $q_g = mysqli_query($koneksi, "SELECT * FROM guru WHERE (" . implode(' OR ', $where_g) . ") LIMIT 1");
        if ($q_g && ($rg = mysqli_fetch_assoc($q_g))) {
            $nama_mapel_terpilih = trim((string)($rg['mapel'] ?? ($rg['nama_mapel'] ?? '')));
        }
    }
}

// Ambil daftar siswa sesuai kelas
$daftar_siswa = [];
if (!empty($kolom_siswa)) {
    $sql_siswa = "SELECT * FROM siswa";
    if ($kelas_get !== '' && $kol_kelas_siswa !== '') {
        $kelas_norm = strtolower(preg_replace('/\s+/', ' ', trim($kelas_get)));
        $kelas_esc  = mysqli_real_escape_string($koneksi, $kelas_norm);
        $sql_siswa .= " WHERE LOWER(TRIM(`$kol_kelas_siswa`)) = '$kelas_esc'
                        OR REPLACE(LOWER(TRIM(`$kol_kelas_siswa`)), '-', ' ') = '$kelas_esc'";
    }
    $sql_siswa .= " ORDER BY 1 ASC";

    $q_siswa = mysqli_query($koneksi, $sql_siswa);
    if ($q_siswa) {
        while ($s = mysqli_fetch_assoc($q_siswa)) {
            $daftar_siswa[] = $s;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card-nilai {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }
        .btn-simpan-nilai {
            background-color: #2c3846;
            border-color: #2c3846;
            color: #ffffff;
            font-weight: 600;
        }
        .btn-simpan-nilai:hover {
            background-color: #1f2833;
            border-color: #1f2833;
            color: #ffffff;
        }
    </style>
</head>
<body>

<?php if (file_exists('navbar.php')) { include 'navbar.php'; } ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-nilai p-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-journal-check me-2"></i>Input Nilai Siswa</h4>
                        <p class="text-muted small mb-0">
                            <?php if ($kelas_get !== '' || $nama_mapel_terpilih !== ''): ?>
                                Kelas: <strong><?= htmlspecialchars(strtoupper($kelas_get ?: '-')); ?></strong>
                                <?php if ($nama_mapel_terpilih !== ''): ?>
                                    | Mapel: <strong><?= htmlspecialchars($nama_mapel_terpilih); ?></strong>
                                <?php endif; ?>
                            <?php else: ?>
                                Masukkan nilai pengetahuan, nilai keterampilan, dan perilaku siswa.
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="<?= $role_sesi === 'guru' ? 'daftar_kelas_guru.php' : 'daftar_mengajar.php' ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>

                <form action="aksi_input_nilai.php" method="POST">
                    <input type="hidden" name="mapel_id" value="<?= htmlspecialchars($mapel_id_get); ?>">
                    <input type="hidden" name="kelas" value="<?= htmlspecialchars($kelas_get); ?>">
                    <input type="hidden" name="mapel" value="<?= htmlspecialchars($nama_mapel_terpilih); ?>">

                    <div class="mb-3">
                        <label for="siswa_id" class="form-label fw-semibold">Pilih Siswa</label>
                        <?php if (!empty($daftar_siswa)): ?>
                            <select name="siswa_id" id="siswa_id" class="form-select" required>
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach ($daftar_siswa as $s): ?>
                                    <?php
                                        $sid   = $s[$pk_siswa] ?? ($s['id'] ?? ($s['ID'] ?? ($s['nis'] ?? '')));
                                        $snama = $s['nama'] ?? ($s['nama_siswa'] ?? 'Tanpa Nama');
                                        $skls  = $s[$kol_kelas_siswa] ?? ($s['kelas'] ?? '');
                                    ?>
                                    <option value="<?= htmlspecialchars((string)$sid) ?>">
                                        <?= htmlspecialchars((string)$snama) ?>
                                        <?= $skls !== '' ? ' - ' . htmlspecialchars((string)$skls) : '' ?>
                                        (ID: <?= htmlspecialchars((string)$sid) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="siswa_id" id="siswa_id" class="form-control" placeholder="Masukkan ID atau NIS siswa..." required>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nilai_pengetahuan" class="form-label fw-semibold">Nilai Pengetahuan (0 - 100)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="nilai_pengetahuan"
                                id="nilai_pengetahuan"
                                class="form-control"
                                placeholder="Contoh: 85"
                                required
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nilai_keterampilan" class="form-label fw-semibold">Nilai Keterampilan (0 - 100)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="nilai_keterampilan"
                                id="nilai_keterampilan"
                                class="form-control"
                                placeholder="Contoh: 88"
                                required
                            >
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="perilaku" class="form-label fw-semibold">Perilaku</label>
                        <input
                            type="text"
                            list="opsi_perilaku"
                            name="perilaku"
                            id="perilaku"
                            class="form-control"
                            placeholder="Contoh: Sangat Baik / Baik / Cukup"
                            required
                        >
                        <datalist id="opsi_perilaku">
                            <option value="Sangat Baik">
                            <option value="Baik">
                            <option value="Cukup">
                            <option value="Perlu Bimbingan">
                        </datalist>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="simpan_nilai" class="btn btn-simpan-nilai py-2">
                            <i class="bi bi-check2-circle me-1"></i>Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>