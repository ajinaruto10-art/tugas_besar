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

// Ambil id guru dari GET atau POST
$id_raw = trim((string)($_GET['id'] ?? $_POST['id'] ?? ''));

if ($id_raw === '' || !ctype_digit($id_raw)) {
    echo "<script>alert('ID guru tidak valid!'); window.location.href='daftar_guru.php';</script>";
    exit;
}

function hg_ambil_kolom(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kolom_guru  = hg_ambil_kolom($koneksi, 'guru');
$kolom_users = hg_ambil_kolom($koneksi, 'users');

$pk_guru  = in_array('id', $kolom_guru, true) ? 'id' : (in_array('ID', $kolom_guru, true) ? 'ID' : 'id');
$pk_users = in_array('id', $kolom_users, true) ? 'id' : (in_array('ID', $kolom_users, true) ? 'ID' : 'id');

$id_esc = mysqli_real_escape_string($koneksi, $id_raw);

// Cek data guru terlebih dahulu
$cek_guru = mysqli_query($koneksi, "SELECT * FROM `guru` WHERE `$pk_guru` = '$id_esc' LIMIT 1");
if (!$cek_guru || mysqli_num_rows($cek_guru) === 0) {
    echo "<script>alert('Data guru tidak ditemukan!'); window.location.href='daftar_guru.php';</script>";
    exit;
}

$data_guru = mysqli_fetch_assoc($cek_guru);
$user_id   = (in_array('user_id', $kolom_guru, true) && !empty($data_guru['user_id']) && ctype_digit((string)$data_guru['user_id']))
    ? mysqli_real_escape_string($koneksi, (string)$data_guru['user_id'])
    : '';

mysqli_begin_transaction($koneksi);

try {
    // 1. Hapus baris dari tabel guru
    $hapus_guru = mysqli_query($koneksi, "DELETE FROM `guru` WHERE `$pk_guru` = '$id_esc' LIMIT 1");
    if (!$hapus_guru) {
        throw new Exception(mysqli_error($koneksi));
    }

    // 2. Hapus akun di tabel users jika terhubung dan role-nya guru (serta tidak dipakai baris guru lain)
    if ($user_id !== '' && !empty($kolom_users)) {
        $cek_pakai_lain = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM `guru` WHERE `user_id` = '$user_id'");
        $jml_lain = 0;
        if ($cek_pakai_lain && $r_lain = mysqli_fetch_assoc($cek_pakai_lain)) {
            $jml_lain = (int)($r_lain['jml'] ?? 0);
        }

        if ($jml_lain === 0) {
            $where_user = "`$pk_users` = '$user_id'";
            if (in_array('role', $kolom_users, true)) {
                $where_user .= " AND LOWER(TRIM(`role`)) = 'guru'";
            }
            $hapus_user = mysqli_query($koneksi, "DELETE FROM `users` WHERE $where_user LIMIT 1");
            if (!$hapus_user) {
                throw new Exception(mysqli_error($koneksi));
            }
        }
    }

    mysqli_commit($koneksi);
    echo "<script>alert('Data guru berhasil dihapus!'); window.location.href='daftar_guru.php';</script>";
    exit;
} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    $pesan = addslashes($e->getMessage());
    echo "<script>alert('Gagal menghapus data guru: {$pesan}'); window.location.href='daftar_guru.php';</script>";
    exit;
}