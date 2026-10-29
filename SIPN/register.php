<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username      = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password      = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nis           = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama          = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kelas         = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);

    $q_user = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'siswa')";
    if (mysqli_query($koneksi, $q_user)) {
        $user_id = mysqli_insert_id($koneksi);
        $q_siswa = "INSERT INTO siswa (nis, nama, kelas, jenis_kelamin, user_id) VALUES ('$nis', '$nama', '$kelas', '$jenis_kelamin', '$user_id')";
        
        if (mysqli_query($koneksi, $q_siswa)) {
            echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location.href='login.php';</script>";
            exit;
        }
    }
    echo "<script>alert('Registrasi Gagal!');</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Register Siswa - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container">
    <div class="card shadow mx-auto" style="max-width: 500px;">
        <div class="card-header bg-primary text-white text-center">
            <h4>Pendaftaran Akun Siswa</h4>
        </div>
        <div class="card-body p-4">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <input type="text" name="kelas" class="form-control" placeholder="Contoh: X PPLG 4" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jenis Kelamin</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_kelamin" value="L" required> Laki-laki
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_kelamin" value="P" required> Perempuan
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Daftar Sekarang</button>
                <div class="text-center mt-3">
                    <a href="login.php">Sudah punya akun? Login di sini</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>