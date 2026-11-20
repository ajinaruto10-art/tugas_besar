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
    header("Location: daftar_guru.php");
    exit;
}

$username_raw  = trim((string)($_POST['username'] ?? ''));
$password_raw  = (string)($_POST['password'] ?? '');
$nama_raw      = trim((string)($_POST['nama'] ?? ''));
$nip_raw       = trim((string)($_POST['nip'] ?? ''));
$jk_raw        = strtolower(trim((string)($_POST['jenis_kelamin'] ?? '')));
$jenis_kelamin = in_array($jk_raw, ['l', 'p'], true) ? $jk_raw : null;
$role          = 'guru';

if ($username_raw === '' || $password_raw === '' || $nama_raw === '') {
    echo "<script>alert('Username, Password, dan Nama Lengkap wajib diisi!'); window.history.back();</script>";
    exit;
}

function ambil_info_kolom_guru(mysqli $koneksi, string $tabel): array {
    $info = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $info[$row['Field']] = $row;
        }
    }
    return $info;
}

function lengkapi_kolom_wajib(array $info_tabel, array &$cols, array &$vals): void {
    foreach ($info_tabel as $nama_kol => $meta) {
        $bersih_cols = array_map(fn($c) => trim($c, '`'), $cols);
        if (in_array($nama_kol, $bersih_cols, true)) {
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
}

$username_esc = mysqli_real_escape_string($koneksi, $username_raw);
$password_esc = mysqli_real_escape_string($koneksi, $password_raw);
$nama_esc     = mysqli_real_escape_string($koneksi, $nama_raw);
$nip_esc      = mysqli_real_escape_string($koneksi, $nip_raw);
$jk_sql       = ($jenis_kelamin !== null) ? ("'" . mysqli_real_escape_string($koneksi, $jenis_kelamin) . "'") : "NULL";
$role_esc     = mysqli_real_escape_string($koneksi, $role);

// Cek apakah username sudah ada di tabel users
$cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE LOWER(TRIM(username)) = LOWER(TRIM('$username_esc')) LIMIT 1");
if ($cek_user && mysqli_num_rows($cek_user) > 0) {
    echo "<script>alert('Username sudah digunakan! Silakan pakai username lain.'); window.history.back();</script>";
    exit;
}

$info_users  = ambil_info_kolom_guru($koneksi, 'users');
$kolom_users = array_keys($info_users);

$cols_u = ['username', 'password', 'role'];
$vals_u = ["'$username_esc'", "'$password_esc'", "'$role_esc'"];

if (in_array('nama', $kolom_users, true)) {
    $cols_u[] = 'nama';
    $vals_u[] = "'$nama_esc'";
}
if (in_array('jenis_kelamin', $kolom_users, true)) {
    $cols_u[] = 'jenis_kelamin';
    $vals_u[] = $jk_sql;
}
lengkapi_kolom_wajib($info_users, $cols_u, $vals_u);

$query_user  = "INSERT INTO users (" . implode(', ', $cols_u) . ") VALUES (" . implode(', ', $vals_u) . ")";
$insert_user = mysqli_query($koneksi, $query_user);

if (!$insert_user) {
    echo "<script>alert('Gagal membuat akun di tabel users: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
    exit;
}

$user_id_baru = (int)mysqli_insert_id($koneksi);
$uid_esc      = mysqli_real_escape_string($koneksi, (string)$user_id_baru);

$info_guru  = ambil_info_kolom_guru($koneksi, 'guru');
$kolom_guru = array_keys($info_guru);

$cols_g = [];
$vals_g = [];

if (in_array('user_id', $kolom_guru, true)) {
    $cols_g[] = 'user_id';
    $vals_g[] = ($user_id_baru > 0) ? "'$uid_esc'" : "NULL";
}
if (in_array('nip', $kolom_guru, true)) {
    $cols_g[] = 'nip';
    $vals_g[] = "'$nip_esc'";
}
if (in_array('nama', $kolom_guru, true)) {
    $cols_g[] = 'nama';
    $vals_g[] = "'$nama_esc'";
}
if (in_array('jenis_kelamin', $kolom_guru, true)) {
    $cols_g[] = 'jenis_kelamin';
    $vals_g[] = $jk_sql;
}
lengkapi_kolom_wajib($info_guru, $cols_g, $vals_g);

$query_guru  = "INSERT INTO guru (" . implode(', ', $cols_g) . ") VALUES (" . implode(', ', $vals_g) . ")";
$insert_guru = mysqli_query($koneksi, $query_guru);

if ($insert_guru) {
    echo "<script>alert('Data guru dan akun login berhasil ditambahkan!'); window.location.href='daftar_guru.php';</script>";
    exit;
} else {
    if (in_array('ID', $kolom_users, true)) {
        mysqli_query($koneksi, "DELETE FROM users WHERE ID = '$uid_esc'");
    } elseif (in_array('id', $kolom_users, true)) {
        mysqli_query($koneksi, "DELETE FROM users WHERE id = '$uid_esc'");
    }
    echo "<script>alert('Gagal menyimpan ke tabel guru: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
    exit;
}