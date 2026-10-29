<?php
session_start();
require_once 'koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = mysqli_real_escape_string($koneksi, trim($_POST['password']));

    // Cari user berdasarkan username di tabel users
    $query  = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' LIMIT 1");
    $data   = mysqli_fetch_assoc($query);

    if ($data) {
        // Cek password (bisa verifikasi password biasa atau password_verify jika di-hash)
        if ($password === $data['password'] || password_verify($password, $data['password'])) {
            
            // Simpan data login dan role otomatis dari database
            $_SESSION['login']    = true;
            $_SESSION['user_id']  = $data['id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['role']     = strtolower(trim($data['role'])); // Mengambil role otomatis dari database

            header("Location: tabel2.php");
            exit;
        } else {
            header("Location: login.php?error=Password yang Anda masukkan salah!");
            exit;
        }
    } else {
        header("Location: login.php?error=Username tidak ditemukan!");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>