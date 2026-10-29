<?php
require 'navbar.php';
require_once 'koneksi.php';

if (!isset($_GET['id'])) {
    header("Location: tabel3.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id'");
$user = mysqli_fetch_assoc($query_user);

if (!$user) {
    echo "<script>alert('User tidak ditemukan!'); window.location.href='tabel3.php';</script>";
    exit;
}

$profil = [];
if ($user['role'] == 'siswa') {
    $q = mysqli_query($koneksi, "SELECT * FROM siswa WHERE user_id = '$id'");
    $profil = mysqli_fetch_assoc($q) ?? [];
} elseif ($user['role'] == 'guru') {
    $q = mysqli_query($koneksi, "SELECT * FROM guru WHERE user_id = '$id'");
    $profil = mysqli_fetch_assoc($q) ?? [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - SIPN SMK Sangkuriang 1 Cimahi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-edit { max-width: 420px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="card card-edit mx-auto border-0">
        <!-- HEADER KARTU (DISAMAKAN DENGAN LOGIN) -->
        <div class="card-header bg-primary text-white text-center py-3" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
            <h4 class="mb-0 fw-bold">EDIT USER</h4>
            <small>Role: <?= ucfirst($user['role']); ?></small>
        </div>
        
        <div class="card-body p-4">
            <form action="aksi_edit.php" method="POST">
                <input type="hidden" name="id" value="<?= $user['id']; ?>">
                <input type="hidden" name="role" value="<?= $user['role']; ?>">

                <!-- USERNAME -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" placeholder="Masukkan username" required>
                </div>

                <!-- PASSWORD -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password baru (opsional)">
                    <small class="text-muted" style="font-size: 0.75rem;">*Kosongkan jika tidak diubah</small>
                </div>

                <!-- NAMA & JENIS KELAMIN (GURU & SISWA) -->
                <?php if ($user['role'] == 'siswa' || $user['role'] == 'guru'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($profil['nama'] ?? ''); ?>" placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Jenis Kelamin</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" <?= (($profil['jenis_kelamin'] ?? '') == 'L') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="jk_l">Laki-laki</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" <?= (($profil['jenis_kelamin'] ?? '') == 'P') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="jk_p">Perempuan</label>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- KHUSUS GURU -->
                <?php if ($user['role'] == 'guru'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">NIP</label>
                        <input type="number" name="nip" class="form-control" value="<?= htmlspecialchars($profil['nip'] ?? ''); ?>" placeholder="Masukkan NIP" required>
                    </div>
                <?php endif; ?>

                <!-- KHUSUS SISWA -->
                <?php if ($user['role'] == 'siswa'): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">NIS</label>
                        <input type="number" name="nis" class="form-control" value="<?= htmlspecialchars($profil['nis'] ?? ''); ?>" placeholder="Masukkan NIS" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kelas</label>
                        <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($profil['kelas'] ?? ''); ?>" placeholder="Masukkan kelas" required>
                    </div>
                <?php endif; ?>

                <!-- TOMBOL ACTION -->
                <button type="submit" name="update" class="btn btn-primary w-100 py-2 mt-2 fw-bold">Update Data</button>
                <button type="button" class="btn btn-secondary w-100 py-2 mt-2 fw-bold" onclick="window.location.href='tabel3.php';">Batal</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>