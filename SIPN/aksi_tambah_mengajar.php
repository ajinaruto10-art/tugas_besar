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
    header("Location: daftar_mengajar.php");
    exit;
}

$kelas   = mysqli_real_escape_string($koneksi, trim((string)($_POST['kelas'] ?? '')));
$guru_id = mysqli_real_escape_string($koneksi, trim((string)($_POST['guru_id'] ?? '')));

if ($kelas === '' || $guru_id === '') {
    echo "<script>alert('Pilih Kelas dan Pilih Guru wajib diisi!'); window.history.back();</script>";
    exit;
}

function atm_kolom(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kol_mapel = atm_kolom($koneksi, 'mapel');
$kol_siswa = atm_kolom($koneksi, 'siswa');

if (empty($kol_mapel)) {
    echo "<script>alert('Tabel mapel tidak ditemukan.'); window.history.back();</script>";
    exit;
}

$pk_mapel = in_array('ID', $kol_mapel, true) ? 'ID' : (in_array('id', $kol_mapel, true) ? 'id' : 'id');
$fk_guru  = in_array('id_guru', $kol_mapel, true) ? 'id_guru' : (in_array('guru_id', $kol_mapel, true) ? 'guru_id' : '');
$ada_kelas_mapel = in_array('kelas', $kol_mapel, true);

// 1. Cari mapel_id berdasarkan prioritas
$mapel_id = '';

// Prioritas 1: cocok guru dan kelas (jika kedua kolom ada di tabel mapel)
if ($fk_guru !== '' && $ada_kelas_mapel) {
    $q_prioritas1 = mysqli_query(
        $koneksi,
        "SELECT `$pk_mapel` AS mid FROM `mapel` WHERE `$fk_guru` = '$guru_id' AND `kelas` = '$kelas' ORDER BY `$pk_mapel` ASC LIMIT 1"
    );
    if ($q_prioritas1 && $r1 = mysqli_fetch_assoc($q_prioritas1)) {
        $mapel_id = mysqli_real_escape_string($koneksi, (string)$r1['mid']);
    }
}

// Prioritas 2: cocok guru saja
if ($mapel_id === '' && $fk_guru !== '') {
    $q_prioritas2 = mysqli_query(
        $koneksi,
        "SELECT `$pk_mapel` AS mid FROM `mapel` WHERE `$fk_guru` = '$guru_id' ORDER BY `$pk_mapel` ASC LIMIT 1"
    );
    if ($q_prioritas2 && $r2 = mysqli_fetch_assoc($q_prioritas2)) {
        $mapel_id = mysqli_real_escape_string($koneksi, (string)$r2['mid']);
    }
}

// Prioritas 3: ambil mapel pertama yang tersedia di tabel mapel
if ($mapel_id === '') {
    $q_prioritas3 = mysqli_query(
        $koneksi,
        "SELECT `$pk_mapel` AS mid FROM `mapel` ORDER BY `$pk_mapel` ASC LIMIT 1"
    );
    if ($q_prioritas3 && $r3 = mysqli_fetch_assoc($q_prioritas3)) {
        $mapel_id = mysqli_real_escape_string($koneksi, (string)$r3['mid']);
    }
}

if ($mapel_id === '') {
    echo "<script>alert('Data mapel belum tersedia! Tambahkan minimal 1 mata pelajaran terlebih dahulu.'); window.location.href='tambah_mapel.php';</script>";
    exit;
}

// 2. Ambil semua siswa yang berada di kelas terpilih
$res_siswa = mysqli_query($koneksi, "SELECT * FROM `siswa` WHERE `kelas` = '$kelas'");
if (!$res_siswa || mysqli_num_rows($res_siswa) === 0) {
    echo "<script>alert('Tidak ada data siswa pada kelas \"$kelas\"!'); window.history.back();</script>";
    exit;
}

$berhasil = 0;
$error_terakhir = '';

while ($s = mysqli_fetch_assoc($res_siswa)) {
    $raw_id_siswa = trim((string)($s['id'] ?? ($s['ID'] ?? '')));
    if ($raw_id_siswa !== '') {
        $id_siswa = mysqli_real_escape_string($koneksi, $raw_id_siswa);

        // Cek agar tidak memasukkan pasangan mapel_id dan siswa_id yang sama berulang
        $cek_ada = mysqli_query(
            $koneksi,
            "SELECT 1 FROM `mengajar` WHERE `mapel_id` = '$mapel_id' AND `siswa_id` = '$id_siswa' LIMIT 1"
        );
        if ($cek_ada && mysqli_num_rows($cek_ada) > 0) {
            continue;
        }

        $query_insert = "INSERT INTO `mengajar` (`mapel_id`, `siswa_id`) VALUES ('$mapel_id', '$id_siswa')";
        if (mysqli_query($koneksi, $query_insert)) {
            $berhasil++;
        } else {
            $error_terakhir = mysqli_error($koneksi);
        }
    }
}

if ($berhasil > 0) {
    echo "<script>alert('Berhasil menyimpan data mengajar untuk kelas " . addslashes($kelas) . " ({$berhasil} siswa)!'); window.location.href='daftar_mengajar.php';</script>";
    exit;
} elseif ($error_terakhir !== '') {
    echo "<script>alert('Gagal menyimpan data mengajar: " . addslashes($error_terakhir) . "'); window.history.back();</script>";
    exit;
} else {
    echo "<script>alert('Data mengajar untuk kelas " . addslashes($kelas) . " sudah terdaftar sebelumnya.'); window.location.href='daftar_mengajar.php';</script>";
    exit;
}