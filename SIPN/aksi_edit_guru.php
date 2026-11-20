<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_POST['update'])) {
    // Validasi hak akses admin
    if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
        header("Location: login.php");
        exit;
    }

    $id       = mysqli_real_escape_string($koneksi, $_POST['id']);
    $user_id   = mysqli_real_escape_string($koneksi, $_POST['user_id']);
    $nip      = mysqli_real_escape_string($koneksi, trim($_POST['nip']));
    $nama     = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $mapel    = mysqli_real_escape_string($koneksi, trim($_POST['mapel']));
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $password = $_POST['password'];

    // Validasi form kosong
    if (empty($nip) || empty($nama) || empty($mapel) || empty($username)) {
        echo "<script>alert('Kolom utama tidak boleh kosong!'); window.history.back();</script>";
        exit;
    }

    // 1. Update data detail guru terlebih dahulu
    $update_guru = mysqli_query($koneksi, "UPDATE guru SET nip = '$nip', nama = '$nama', mapel = '$mapel' WHERE id = '$id'");

    if ($update_guru) {
        // 2. Cek apakah user_id kosong (NULL). Jika kosong, buatkan akun baru otomatis di tabel users
        if (empty($user_id) || $user_id == 0 || $user_id === 'NULL') {
            $pw_hashed = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT);
            $query_new_user = mysqli_query($koneksi, "INSERT INTO users (username, password, role) VALUES ('$username', '$pw_hashed', 'guru')");
            
            if ($query_new_user) {
                $new_user_id = mysqli_insert_id($koneksi);
                // Hubungkan user_id baru ke tabel guru
                mysqli_query($koneksi, "UPDATE guru SET user_id = '$new_user_id' WHERE id = '$id'");
            }
        } else {
            // Jika sudah punya akun, update username dan password (jika password diisi)
            if (!empty($password)) {
                $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($koneksi, "UPDATE users SET username = '$username', password = '$password_hashed' WHERE id = '$user_id'");
            } else {
                mysqli_query($koneksi, "UPDATE users SET username = '$username' WHERE id = '$user_id'");
            }
        }

        echo "<script>alert('Data guru berhasil diperbarui!'); window.location.href='daftar_guru.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data guru: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
} else {
    header("Location: daftar_guru.php");
    exit;
}
?>