<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login & role
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
if ($role !== 'guru' && $role !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href='login.php';</script>";
    exit;
}

function ns_kolom(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kol_siswa  = ns_kolom($koneksi, 'siswa');
$kol_mapel  = ns_kolom($koneksi, 'mapel');
$kol_raport = ns_kolom($koneksi, 'nilail_raport');
$kol_nilai  = ns_kolom($koneksi, 'nilai');

$tabel_nilai = !empty($kol_raport) ? 'nilail_raport' : (!empty($kol_nilai) ? 'nilai' : '');
$kol_tujuan  = !empty($kol_raport) ? $kol_raport : $kol_nilai;

$pk_siswa = in_array('ID', $kol_siswa, true) ? 'ID' : (in_array('id', $kol_siswa, true) ? 'id' : 'id');
$pk_mapel = in_array('ID', $kol_mapel, true) ? 'ID' : (in_array('id', $kol_mapel, true) ? 'id' : 'ID');
$pk_nilai = in_array('id', $kol_tujuan, true) ? 'id' : (in_array('ID', $kol_tujuan, true) ? 'ID' : 'id');

$kelas_terpilih = trim((string)($_GET['kelas'] ?? ''));
$mapel_terpilih = trim((string)($_GET['mapel_id'] ?? ''));

// Ambil nama mata pelajaran jika mapel_id dikirim
$nama_mapel_tampil = 'Mata Pelajaran';
$kol_nama_mp = in_array('nama_mapel', $kol_mapel, true) ? 'nama_mapel' : (in_array('mapel', $kol_mapel, true) ? 'mapel' : '');
if (!empty($kol_mapel) && $kol_nama_mp !== '' && $mapel_terpilih !== '' && ctype_digit($mapel_terpilih)) {
    $mid_esc = mysqli_real_escape_string($koneksi, $mapel_terpilih);
    $qm = mysqli_query($koneksi, "SELECT `$kol_nama_mp` AS mnama FROM `mapel` WHERE `$pk_mapel` = '$mid_esc' LIMIT 1");
    if ($qm && $rm = mysqli_fetch_assoc($qm)) {
        $nama_mapel_tampil = (string)$rm['mnama'];
    }
}

// Ambil data siswa dan nilai (jika ada di tabel nilai / nilail_raport)
$rows = [];
if (!empty($kol_siswa)) {
    $kol_nama_s = in_array('nama', $kol_siswa, true)
        ? 's.`nama`'
        : (in_array('nama_siswa', $kol_siswa, true) ? 's.`nama_siswa`' : "''");

    $where_s = "1=1";
    if ($kelas_terpilih !== '' && in_array('kelas', $kol_siswa, true)) {
        $kls_esc = mysqli_real_escape_string($koneksi, $kelas_terpilih);
        $where_s = "TRIM(s.`kelas`) = '$kls_esc'";
    }

    $join_nilai   = "";
    $sel_nilai_id = "NULL AS nilai_id";
    $sel_kkm      = "NULL AS kkm";
    $sel_peng     = "NULL AS pengetahuan";
    $sel_ket      = "NULL AS keterampilan";
    $sel_ket_teks = "NULL AS ket_teks";

    if ($tabel_nilai !== '' && in_array('siswa_id', $kol_tujuan, true)) {
        $cond_n = "n.`siswa_id` = s.`$pk_siswa`";
        if ($mapel_terpilih !== '' && ctype_digit($mapel_terpilih) && in_array('mapel_id', $kol_tujuan, true)) {
            $mid_esc = mysqli_real_escape_string($koneksi, $mapel_terpilih);
            $cond_n .= " AND n.`mapel_id` = '$mid_esc'";
        }
        $join_nilai   = "LEFT JOIN `$tabel_nilai` n ON $cond_n";
        $sel_nilai_id = "MAX(n.`$pk_nilai`) AS nilai_id";

        if (in_array('nilai_kkm', $kol_tujuan, true)) $sel_kkm = "MAX(n.`nilai_kkm`) AS kkm";
        elseif (in_array('kkm', $kol_tujuan, true)) $sel_kkm = "MAX(n.`kkm`) AS kkm";

        if (in_array('nilial_pengetauan', $kol_tujuan, true)) $sel_peng = "MAX(n.`nilial_pengetauan`) AS pengetahuan";
        elseif (in_array('nilai_pengetahuan', $kol_tujuan, true)) $sel_peng = "MAX(n.`nilai_pengetahuan`) AS pengetahuan";
        elseif (in_array('pengetahuan', $kol_tujuan, true)) $sel_peng = "MAX(n.`pengetahuan`) AS pengetahuan";

        if (in_array('nilail_keterampilan', $kol_tujuan, true)) $sel_ket = "MAX(n.`nilail_keterampilan`) AS keterampilan";
        elseif (in_array('nilai_keterampilan', $kol_tujuan, true)) $sel_ket = "MAX(n.`nilai_keterampilan`) AS keterampilan";
        elseif (in_array('keterampilan', $kol_tujuan, true)) $sel_ket = "MAX(n.`keterampilan`) AS keterampilan";

        if (in_array('keterangan', $kol_tujuan, true)) $sel_ket_teks = "MAX(n.`keterangan`) AS ket_teks";
    }

    $group_s = ["s.`$pk_siswa`"];
    if (in_array('nama', $kol_siswa, true)) $group_s[] = "s.`nama`";
    if (in_array('nama_siswa', $kol_siswa, true)) $group_s[] = "s.`nama_siswa`";

    $sql_siswa = "
        SELECT
            s.`$pk_siswa` AS siswa_id,
            $kol_nama_s AS nama_siswa,
            $sel_nilai_id,
            $sel_kkm,
            $sel_peng,
            $sel_ket,
            $sel_ket_teks
        FROM `siswa` s
        $join_nilai
        WHERE $where_s
        GROUP BY " . implode(', ', array_unique($group_s)) . "
        ORDER BY nama_siswa ASC
    ";

    $res_s = mysqli_query($koneksi, $sql_siswa);
    if ($res_s) {
        while ($rs = mysqli_fetch_assoc($res_s)) {
            $rows[] = $rs;
        }
    }
}
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

<?php if (file_exists('navbar.php')) { include 'navbar.php'; } ?>

<div class="container my-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="fw-bold mb-1">Daftar Nilai Siswa</h2>
            <div class="text-muted small">
                Kelas: <strong><?= htmlspecialchars($kelas_terpilih !== '' ? $kelas_terpilih : 'Semua Kelas'); ?></strong>
                | Mata Pelajaran: <strong><?= htmlspecialchars($nama_mapel_tampil); ?></strong>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="daftar_kelas_guru.php" class="btn btn-secondary btn-sm fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kelas
            </a>
            <a href="tambah_nilai.php?kelas=<?= urlencode($kelas_terpilih); ?>&mapel_id=<?= urlencode($mapel_terpilih); ?>" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> Tambah Nilai
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Siswa</th>
                            <th class="text-center" style="width: 90px;">KKM</th>
                            <th class="text-center" style="width: 130px;">Pengetahuan</th>
                            <th class="text-center" style="width: 130px;">Keterampilan</th>
                            <th class="text-center" style="width: 120px;">Nilai Akhir</th>
                            <th style="width: 130px;">Keterangan</th>
                            <th class="text-center" style="width: 190px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($rows as $row): ?>
                                <?php
                                $punya_nilai = ($row['nilai_id'] !== null && $row['nilai_id'] !== '')
                                    || ($row['pengetahuan'] !== null && $row['pengetahuan'] !== '')
                                    || ($row['keterampilan'] !== null && $row['keterampilan'] !== '');

                                $kkm_val  = ($row['kkm'] !== null && $row['kkm'] !== '') ? (float)$row['kkm'] : 75;
                                $peng_val = ($row['pengetahuan'] !== null && $row['pengetahuan'] !== '') ? (float)$row['pengetahuan'] : null;
                                $ket_val  = ($row['keterampilan'] !== null && $row['keterampilan'] !== '') ? (float)$row['keterampilan'] : null;

                                $akhir_val = ($peng_val !== null && $ket_val !== null)
                                    ? round(($peng_val + $ket_val) / 2)
                                    : null;

                                $ket_tampil = trim((string)($row['ket_teks'] ?? ''));
                                if ($ket_tampil === '') {
                                    $ket_tampil = $akhir_val !== null ? ($akhir_val >= $kkm_val ? 'Lulus' : 'Tidak Lulus') : '-';
                                }

                                $sid = urlencode((string)$row['siswa_id']);
                                $nid = urlencode((string)($row['nilai_id'] ?? ''));
                                $mid = urlencode($mapel_terpilih);
                                $kls = urlencode($kelas_terpilih);
                                ?>
                                <tr>
                                    <td class="fw-bold"><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars((string)($row['nama_siswa'] ?? '-')); ?></td>
                                    <td class="text-center"><?= $punya_nilai ? htmlspecialchars((string)$kkm_val) : '-'; ?></td>
                                    <td class="text-center">
                                        <?php if ($peng_val !== null): ?>
                                            <span class="badge bg-primary px-3 py-2"><?= htmlspecialchars((string)$peng_val); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Belum ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($ket_val !== null): ?>
                                            <span class="badge bg-primary px-3 py-2"><?= htmlspecialchars((string)$ket_val); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Belum ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($akhir_val !== null): ?>
                                            <span class="badge bg-success px-3 py-2"><?= htmlspecialchars((string)$akhir_val); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Belum ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($ket_tampil); ?></td>
                                    <td class="text-center">
                                        <a href="edit_nilai.php?id=<?= $nid; ?>&siswa_id=<?= $sid; ?>&mapel_id=<?= $mid; ?>&kelas=<?= $kls; ?>" class="btn btn-warning btn-sm fw-semibold text-dark">
                                            Edit Nilai
                                        </a>
                                        <?php if (!$punya_nilai): ?>
                                            <a href="tambah_nilai.php?siswa_id=<?= $sid; ?>&mapel_id=<?= $mid; ?>&kelas=<?= $kls; ?>" class="btn btn-primary btn-sm fw-semibold">
                                                Tambah
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada data siswa di kelas ini.</td>
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