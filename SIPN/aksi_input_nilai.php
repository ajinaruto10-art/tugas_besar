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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: input_nilai.php");
    exit;
}

$siswa_id_raw     = trim((string)($_POST['siswa_id'] ?? ''));
$mapel_id_raw     = trim((string)($_POST['mapel_id'] ?? ''));
$kelas_raw        = trim((string)($_POST['kelas'] ?? ''));
$mapel_raw        = trim((string)($_POST['mapel'] ?? ''));
$pengetahuan_raw  = trim((string)($_POST['nilai_pengetahuan'] ?? ''));
$keterampilan_raw = trim((string)($_POST['nilai_keterampilan'] ?? ''));
$perilaku_raw     = trim((string)($_POST['perilaku'] ?? ''));
$user_id_sesi     = $_SESSION['user_id'] ?? null;

if ($siswa_id_raw === '' || $pengetahuan_raw === '' || $keterampilan_raw === '' || $perilaku_raw === '') {
    echo "<script>alert('Siswa, Nilai Pengetahuan, Nilai Keterampilan, dan Perilaku wajib diisi!'); window.history.back();</script>";
    exit;
}

if (
    !is_numeric($pengetahuan_raw) || (float)$pengetahuan_raw < 0 || (float)$pengetahuan_raw > 100 ||
    !is_numeric($keterampilan_raw) || (float)$keterampilan_raw < 0 || (float)$keterampilan_raw > 100
) {
    echo "<script>alert('Nilai pengetahuan dan keterampilan harus berupa angka antara 0 sampai 100!'); window.history.back();</script>";
    exit;
}

function ambil_info_kolom_aksi(mysqli $koneksi, string $tabel): array {
    $info = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $info[$row['Field']] = $row;
        }
    }
    return $info;
}

$info_nilai = ambil_info_kolom_aksi($koneksi, 'nilai');
if (empty($info_nilai)) {
    die("Tabel 'nilai' belum ditemukan atau query gagal: " . mysqli_error($koneksi));
}
$kolom_nilai = array_keys($info_nilai);

$info_siswa  = ambil_info_kolom_aksi($koneksi, 'siswa');
$kolom_siswa = array_keys($info_siswa);

// Ambil nama siswa dan kelas dari tabel siswa jika belum ada
$nama_siswa_db = '';
if (!empty($kolom_siswa)) {
    $sid_esc = mysqli_real_escape_string($koneksi, $siswa_id_raw);
    $syarat_s = [];
    foreach (['ID', 'id', 'nis', 'nisn'] as $k_s) {
        if (in_array($k_s, $kolom_siswa, true)) {
            $syarat_s[] = "`$k_s` = '$sid_esc'";
        }
    }
    if (!empty($syarat_s)) {
        $q_s = mysqli_query($koneksi, "SELECT * FROM siswa WHERE (" . implode(' OR ', $syarat_s) . ") LIMIT 1");
        if ($q_s && ($rs = mysqli_fetch_assoc($q_s))) {
            $nama_siswa_db = trim((string)($rs['nama_siswa'] ?? ($rs['nama'] ?? '')));
            if ($kelas_raw === '') {
                $kelas_raw = trim((string)($rs['kelas'] ?? ($rs['nama_kelas'] ?? '')));
            }
        }
    }
}

$n_pengh     = (float)$pengetahuan_raw;
$n_ketr      = (float)$keterampilan_raw;
$n_rata_rata = round(($n_pengh + $n_ketr) / 2, 2);

$siswa_esc      = mysqli_real_escape_string($koneksi, $siswa_id_raw);
$nama_siswa_esc = mysqli_real_escape_string($koneksi, $nama_siswa_db);
$mapel_id_esc   = mysqli_real_escape_string($koneksi, $mapel_id_raw);
$kelas_esc      = mysqli_real_escape_string($koneksi, $kelas_raw);
$mapel_esc      = mysqli_real_escape_string($koneksi, $mapel_raw);
$pengh_esc      = mysqli_real_escape_string($koneksi, (string)$n_pengh);
$ketr_esc       = mysqli_real_escape_string($koneksi, (string)$n_ketr);
$rata_esc       = mysqli_real_escape_string($koneksi, (string)$n_rata_rata);
$perilaku_esc   = mysqli_real_escape_string($koneksi, $perilaku_raw);

$cols = [];
$vals = [];

// Kolom siswa
if (in_array('siswa_id', $kolom_nilai, true)) {
    $cols[] = 'siswa_id';
    $vals[] = "'$siswa_esc'";
} elseif (in_array('id_siswa', $kolom_nilai, true)) {
    $cols[] = 'id_siswa';
    $vals[] = "'$siswa_esc'";
} elseif (in_array('nis', $kolom_nilai, true)) {
    $cols[] = 'nis';
    $vals[] = "'$siswa_esc'";
}

if (in_array('nama_siswa', $kolom_nilai, true)) {
    $cols[] = 'nama_siswa';
    $vals[] = "'$nama_siswa_esc'";
} elseif (in_array('nama', $kolom_nilai, true)) {
    $cols[] = 'nama';
    $vals[] = "'$nama_siswa_esc'";
}

// Kolom mapel & kelas
if ($mapel_id_raw !== '') {
    if (in_array('mapel_id', $kolom_nilai, true)) {
        $cols[] = 'mapel_id';
        $vals[] = "'$mapel_id_esc'";
    } elseif (in_array('id_mapel', $kolom_nilai, true)) {
        $cols[] = 'id_mapel';
        $vals[] = "'$mapel_id_esc'";
    }
}

if ($kelas_raw !== '') {
    if (in_array('kelas', $kolom_nilai, true)) {
        $cols[] = 'kelas';
        $vals[] = "'$kelas_esc'";
    } elseif (in_array('nama_kelas', $kolom_nilai, true)) {
        $cols[] = 'nama_kelas';
        $vals[] = "'$kelas_esc'";
    }
}

if ($mapel_raw !== '') {
    if (in_array('mapel', $kolom_nilai, true)) {
        $cols[] = 'mapel';
        $vals[] = "'$mapel_esc'";
    } elseif (in_array('nama_mapel', $kolom_nilai, true)) {
        $cols[] = 'nama_mapel';
        $vals[] = "'$mapel_esc'";
    }
}

// Kolom nilai pengetahuan, keterampilan, dan rata-rata
if (in_array('nilai_pengetahuan', $kolom_nilai, true)) {
    $cols[] = 'nilai_pengetahuan';
    $vals[] = "'$pengh_esc'";
}

if (in_array('nilai_keterampilan', $kolom_nilai, true)) {
    $cols[] = 'nilai_keterampilan';
    $vals[] = "'$ketr_esc'";
}

if (in_array('nilai', $kolom_nilai, true)) {
    $cols[] = 'nilai';
    $vals[] = "'$rata_esc'";
}

if (in_array('nilai_akhir', $kolom_nilai, true)) {
    $cols[] = 'nilai_akhir';
    $vals[] = "'$rata_esc'";
}

// Kolom perilaku / sikap / keterangan
if (in_array('perilaku', $kolom_nilai, true)) {
    $cols[] = 'perilaku';
    $vals[] = "'$perilaku_esc'";
} elseif (in_array('sikap', $kolom_nilai, true)) {
    $cols[] = 'sikap';
    $vals[] = "'$perilaku_esc'";
} elseif (in_array('keterangan', $kolom_nilai, true)) {
    $cols[] = 'keterangan';
    $vals[] = "'$perilaku_esc'";
}

// Kolom user_id / guru_id
if (!empty($user_id_sesi)) {
    $uid_esc = mysqli_real_escape_string($koneksi, (string)$user_id_sesi);
    if (in_array('user_id', $kolom_nilai, true)) {
        $cols[] = 'user_id';
        $vals[] = "'$uid_esc'";
    } elseif (in_array('guru_id', $kolom_nilai, true)) {
        $cols[] = 'guru_id';
        $vals[] = "'$uid_esc'";
    }
}

// Pengaman kolom NOT NULL lain yang tidak punya default
foreach ($info_nilai as $nama_kol => $meta) {
    if (in_array($nama_kol, $cols, true)) {
        continue;
    }
    $is_null    = (($meta['Null'] ?? '') === 'YES');
    $has_def    = array_key_exists('Default', $meta) && $meta['Default'] !== null;
    $is_autoinc = (stripos((string)($meta['Extra'] ?? ''), 'auto_increment') !== false);

    if (!$is_null && !$has_def && !$is_autoinc) {
        $tipe = strtolower((string)($meta['Type'] ?? ''));
        $cols[] = "`$nama_kol`";
        if (preg_match('/int|decimal|float|double/', $tipe)) {
            $vals[] = "'0'";
        } else {
            $vals[] = "''";
        }
    }
}

$sql = "INSERT INTO nilai (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")";
$simpan = mysqli_query($koneksi, $sql);

if ($simpan) {
    $tujuan = ($role_sesi === 'guru') ? 'daftar_kelas_guru.php' : 'tabel2.php';
    echo "<script>alert('Nilai berhasil disimpan!'); window.location.href='" . $tujuan . "';</script>";
    exit;
} else {
    echo "<script>alert('Gagal menyimpan nilai: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
    exit;
}