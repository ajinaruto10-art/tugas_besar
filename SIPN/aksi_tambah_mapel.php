<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek hak akses admin
if (!isset($_SESSION['login']) || strtolower(trim((string)($_SESSION['role'] ?? ''))) !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mapel.php");
    exit;
}

$kode_mapel_raw = trim((string)($_POST['kode_mapel'] ?? ''));
$nama_mapel_raw = trim((string)($_POST['nama_mapel'] ?? ''));
$guru_id_raw    = trim((string)($_POST['guru_id'] ?? ''));

if ($kode_mapel_raw === '' || $nama_mapel_raw === '') {
    echo "<script>alert('Kode Mata Pelajaran dan Nama Mata Pelajaran wajib diisi!'); window.history.back();</script>";
    exit;
}

$kode_mapel = mysqli_real_escape_string($koneksi, $kode_mapel_raw);
$nama_mapel = mysqli_real_escape_string($koneksi, $nama_mapel_raw);

// Cek kolom yang ada di tabel guru
$kolom_guru_res = mysqli_query($koneksi, "SHOW COLUMNS FROM guru");
$kolom_guru = [];
if ($kolom_guru_res) {
    while ($kg = mysqli_fetch_assoc($kolom_guru_res)) {
        $kolom_guru[] = $kg['Field'];
    }
}
$pk_guru = in_array('id', $kolom_guru, true) ? 'id' : (in_array('ID', $kolom_guru, true) ? 'ID' : 'id');

// Ambil data guru jika dipilih (mendukung kecocokan lewat id maupun user_id)
$row_guru = null;
$target_guru_pk = '';
if ($guru_id_raw !== '') {
    $guru_id_esc = mysqli_real_escape_string($koneksi, $guru_id_raw);
    $where_guru = "`$pk_guru` = '$guru_id_esc'";
    if (in_array('user_id', $kolom_guru, true)) {
        $where_guru = "(`$pk_guru` = '$guru_id_esc' OR user_id = '$guru_id_esc')";
    }
    $cek_guru = mysqli_query($koneksi, "SELECT * FROM guru WHERE $where_guru LIMIT 1");
    if ($cek_guru && mysqli_num_rows($cek_guru) > 0) {
        $row_guru = mysqli_fetch_assoc($cek_guru);
        $target_guru_pk = mysqli_real_escape_string($koneksi, (string)($row_guru[$pk_guru] ?? ''));
    }
}

$nama_guru_terpilih = mysqli_real_escape_string($koneksi, (string)($row_guru['nama'] ?? ''));

// Cek kolom yang ada di tabel mapel
$kolom_res = mysqli_query($koneksi, "SHOW COLUMNS FROM mapel");
$kolom_ada = [];
if ($kolom_res) {
    while ($k = mysqli_fetch_assoc($kolom_res)) {
        $kolom_ada[] = $k['Field'];
    }
}

$fields = [];
$values = [];

if (in_array('kode_mapel', $kolom_ada, true)) {
    $fields[] = 'kode_mapel';
    $values[] = "'$kode_mapel'";
} elseif (in_array('kode', $kolom_ada, true)) {
    $fields[] = 'kode';
    $values[] = "'$kode_mapel'";
}

if (in_array('nama_mapel', $kolom_ada, true)) {
    $fields[] = 'nama_mapel';
    $values[] = "'$nama_mapel'";
} elseif (in_array('mapel', $kolom_ada, true)) {
    $fields[] = 'mapel';
    $values[] = "'$nama_mapel'";
}

if ($target_guru_pk !== '') {
    if (in_array('guru_id', $kolom_ada, true)) {
        $fields[] = 'guru_id';
        $values[] = "'$target_guru_pk'";
    } elseif (in_array('id_guru', $kolom_ada, true)) {
        $fields[] = 'id_guru';
        $values[] = "'$target_guru_pk'";
    } elseif (in_array('guru_pengampu', $kolom_ada, true)) {
        $fields[] = 'guru_pengampu';
        $values[] = "'$nama_guru_terpilih'";
    } elseif (in_array('nama_guru', $kolom_ada, true)) {
        $fields[] = 'nama_guru';
        $values[] = "'$nama_guru_terpilih'";
    }
}

if (empty($fields)) {
    die("Struktur kolom tabel mapel tidak dikenali.");
}

$query_insert = "INSERT INTO mapel (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
$insert = mysqli_query($koneksi, $query_insert);

if ($insert) {
    // Update kolom mapel di tabel guru untuk guru mana pun yang dipilih
    if ($row_guru !== null && $target_guru_pk !== '' && in_array('mapel', $kolom_guru, true)) {
        $mapel_lama = trim((string)($row_guru['mapel'] ?? ''));
        if ($mapel_lama === '') {
            $mapel_baru = $nama_mapel_raw;
        } else {
            $daftar_lama = array_map('trim', explode(',', $mapel_lama));
            $sudah_ada = false;
            foreach ($daftar_lama as $item) {
                if (mb_strtolower($item) === mb_strtolower($nama_mapel_raw)) {
                    $sudah_ada = true;
                    break;
                }
            }
            $mapel_baru = $sudah_ada ? $mapel_lama : ($mapel_lama . ', ' . $nama_mapel_raw);
        }

        $mapel_baru_esc = mysqli_real_escape_string($koneksi, $mapel_baru);
        mysqli_query($koneksi, "UPDATE guru SET mapel = '$mapel_baru_esc' WHERE `$pk_guru` = '$target_guru_pk'");
    }

    // Catat relasi mengajar hanya jika kolom guru_id dan mapel_id memang tersedia di tabel mengajar
    if ($target_guru_pk !== '') {
        $cek_mengajar = mysqli_query($koneksi, "SHOW TABLES LIKE 'mengajar'");
        if ($cek_mengajar && mysqli_num_rows($cek_mengajar) > 0) {
            $mapel_id_baru = (int)mysqli_insert_id($koneksi);
            $kol_m_res = mysqli_query($koneksi, "SHOW COLUMNS FROM mengajar");
            $kol_m = [];
            if ($kol_m_res) {
                while ($km = mysqli_fetch_assoc($kol_m_res)) {
                    $kol_m[] = $km['Field'];
                }
            }
            if ($mapel_id_baru > 0 && in_array('guru_id', $kol_m, true) && in_array('mapel_id', $kol_m, true)) {
                $mid_esc = mysqli_real_escape_string($koneksi, (string)$mapel_id_baru);
                mysqli_query($koneksi, "INSERT INTO mengajar (guru_id, mapel_id) VALUES ('$target_guru_pk', '$mid_esc')");
            }
        }
    }

    echo "<script>alert('Mata pelajaran berhasil ditambahkan dan disinkronkan ke data guru!'); window.location.href='mapel.php';</script>";
    exit;
} else {
    echo "<script>alert('Gagal menambahkan mata pelajaran: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
    exit;
}