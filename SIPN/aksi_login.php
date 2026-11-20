<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

if (!isset($koneksi) || !$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username_input = trim((string)($_POST['username'] ?? ''));
$password_input = (string)($_POST['password'] ?? '');

if ($username_input === '' || $password_input === '') {
    echo "<script>alert('Username dan password wajib diisi!'); window.location.href='login.php';</script>";
    exit;
}

$username_lower = strtolower($username_input);
$username_esc   = mysqli_real_escape_string($koneksi, $username_lower);

$sql   = "SELECT * FROM users WHERE LOWER(TRIM(username)) = '$username_esc' LIMIT 1";
$query = mysqli_query($koneksi, $sql);

if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}

$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Username tidak ditemukan!'); window.location.href='login.php';</script>";
    exit;
}

$password_db = (string)($data['password'] ?? '');
$id_user     = $data['ID'] ?? ($data['id'] ?? '');
$role        = strtolower(trim((string)($data['role'] ?? '')));

$login_berhasil = false;

// Cek password hash maupun plain text
if ($password_db !== '' && password_verify($password_input, $password_db)) {
    $login_berhasil = true;
} elseif ($password_db !== '' && hash_equals($password_db, $password_input)) {
    $login_berhasil = true;
}

if ($login_berhasil) {
    session_regenerate_id(true);
    $_SESSION['login']    = true;
    $_SESSION['user_id']  = $id_user;
    $_SESSION['username'] = $data['username'];
    $_SESSION['role']     = $role;

    if ($role === 'admin') {
        header("Location: daftar_mengajar.php");
    } elseif ($role === 'guru') {
        header("Location: daftar_kelas_guru.php");
    } else {
        header("Location: tabel2.php");
    }
    exit;
} else {
    echo "<script>alert('Password salah!'); window.location.href='login.php';</script>";
    exit;
}