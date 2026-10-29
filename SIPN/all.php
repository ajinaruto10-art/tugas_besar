<?php
require_once 'navbar.php';
require_once 'koneksi.php';

// Proteksi Khusus Admin
if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
    echo "<script>alert('Akses khusus Admin!'); window.location.href='dhasboard.php';</script>";
    exit;
}

$msg = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password']; 
    $role     = mysqli_real_escape_string($koneksi, $_POST['role']);
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $nip      = mysqli_real_escape_string($koneksi, $_POST['nip']);

    // Cek ketersediaan username
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $msg = "Username sudah terdaftar! Pilih username lain.";
        $msg_type = "danger";
    } else {
        // 1. Insert ke tabel users
        $ins_user = mysqli_query($koneksi, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')");
        
        if ($ins_user) {
            $user_id = mysqli_insert_id($koneksi);

            // 2. Insert ke tabel guru jika role yang dipilih adalah Guru
            if ($role == 'guru') {
                mysqli_query($koneksi, "INSERT INTO guru (user_id, nip, nama) VALUES ('$user_id', '$nip', '$nama')");
            }

            echo "<script>alert('Akun $role berhasil dibuat!'); window.location.href='tabel3.php';</script>";
            exit;
        } else {
            $msg = "Gagal membuat akun: " . mysqli_error($koneksi);
            $msg_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Akun Guru & Admin - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Mengatur tampilan input berdasarkan role yang dipilih
        function toggleRoleFields() {
            var role = document.getElementById("roleSelect").value;
            var fieldGuru = document.getElementById("fieldGuru");

            if (role === 'admin') {
                fieldGuru.style.display = "none";
            } else if (role === 'guru') {
                fieldGuru.style.display = "block";
            }
        }
    </script>
</head>
<body class="bg-light" onload="toggleRoleFields()">

<div class="container mt-5 mb-5">
    <div class="card shadow border-0 col-md-6 mx-auto">
        <div class="card-header bg-primary text-white text-center py-3">
            <h5 class="mb-0 fw-bold">Form Tambah Akun (Guru / Admin)</h5>
        </div>
        <div class="card-body p-4">

            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?= $msg_type; ?>"><?= $msg; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <!-- PILIH ROLE (HANYA GURU DAN ADMIN) -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Role / Level</label>
                    <select name="role" id="roleSelect" class="form-select" onchange="toggleRoleFields()" required>
                        <option value="guru">Guru</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <!-- USERNAME & PASSWORD -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <!-- FIELD KHUSUS GURU (NAMA & NIP) -->
                <div id="fieldGuru">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap Guru</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap guru">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">NIP (Nomor Induk)</label>
                        <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP guru">
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="tabel3.php" class="btn btn-secondary">← Kembali ke Tabel User</a>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan User Baru</button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>