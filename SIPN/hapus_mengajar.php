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

$id_raw       = trim((string)($_GET['id'] ?? $_POST['id'] ?? ''));
$mapel_id_raw = trim((string)($_GET['mapel_id'] ?? $_POST['mapel_id'] ?? ''));

if (($id_raw === '' || !ctype_digit($id_raw)) && ($mapel_id_raw === '' || !ctype_digit($mapel_id_raw))) {
    echo "<script>alert('ID data mengajar tidak valid!'); window.location.href='daftar_mengajar.php';</script>";
    exit;
}

function hm_kolom(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kol_mg    = hm_kolom($koneksi, 'mengajar');
$kol_mapel = hm_kolom($koneksi, 'mapel');

$pk_mg    = in_array('id', $kol_mg, true) ? 'id' : (in_array('ID', $kol_mg, true) ? 'ID' : 'id');
$pk_mapel = in_array('ID', $kol_mapel, true) ? 'ID' : (in_array('id', $kol_mapel, true) ? 'id' : 'ID');

mysqli_begin_transaction($koneksi);

try {
    $dihapus = false;

    if (!empty($kol_mg)) {
        if ($id_raw !== '' && ctype_digit($id_raw)) {
            $id_esc = mysqli_real_escape_string($koneksi, $id_raw);
            $q1 = mysqli_query($koneksi, "DELETE FROM `mengajar` WHERE `$pk_mg` = '$id_esc'");
            if (!$q1) {
                throw new Exception(mysqli_error($koneksi));
            }
            if (mysqli_affected_rows($koneksi) > 0) {
                $dihapus = true;
            }
        }

        if ($mapel_id_raw !== '' && ctype_digit($mapel_id_raw) && in_array('mapel_id', $kol_mg, true)) {
            $mid_mg_esc = mysqli_real_escape_string($koneksi, $mapel_id_raw);
            $q2 = mysqli_query($koneksi, "DELETE FROM `mengajar` WHERE `mapel_id` = '$mid_mg_esc'");
            if (!$q2) {
                throw new Exception(mysqli_error($koneksi));
            }
            if (mysqli_affected_rows($koneksi) > 0) {
                $dihapus = true;
            }
        }
    }

    // Hapus juga baris di tabel mapel jika mapel_id dikirim dan tidak lagi dipakai di mengajar
    if ($mapel_id_raw !== '' && ctype_digit($mapel_id_raw) && !empty($kol_mapel)) {
        $mid_esc = mysqli_real_escape_string($koneksi, $mapel_id_raw);
        $sisa_mg = 0;

        if (!empty($kol_mg) && in_array('mapel_id', $kol_mg, true)) {
            $cek_sisa = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM `mengajar` WHERE `mapel_id` = '$mid_esc'");
            if ($cek_sisa && $rs = mysqli_fetch_assoc($cek_sisa)) {
                $sisa_mg = (int)($rs['jml'] ?? 0);
            }
        }

        if ($sisa_mg === 0) {
            $q3 = mysqli_query($koneksi, "DELETE FROM `mapel` WHERE `$pk_mapel` = '$mid_esc' LIMIT 1");
            if (!$q3) {
                throw new Exception(mysqli_error($koneksi));
            }
            if (mysqli_affected_rows($koneksi) > 0) {
                $dihapus = true;
            }
        }
    }

    mysqli_commit($koneksi);

    if ($dihapus) {
        echo "<script>alert('Data berhasil dihapus dari tabel!'); window.location.href='daftar_mengajar.php';</script>";
    } else {
        echo "<script>alert('Data sudah tidak ada atau sudah terhapus sebelumnya.'); window.location.href='daftar_mengajar.php';</script>";
    }
    exit;
} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    $err = addslashes($e->getMessage());
    echo "<script>alert('Gagal menghapus data: {$err}'); window.location.href='daftar_mengajar.php';</script>";
    exit;
}