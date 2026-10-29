<?php
require_once 'navbar.php';
require_once 'koneksi.php';

$user_id  = $_SESSION['user_id'];
$role     = $_SESSION['role'];
$username = $_SESSION['username'];

// Ambil detail profil berdasarkan role
$profil = [];
if ($role == 'siswa') {
    $q = mysqli_query($koneksi, "SELECT * FROM siswa WHERE user_id = '$user_id'");
    $profil = mysqli_fetch_assoc($q) ?? [];
} elseif ($role == 'guru') {
    $q = mysqli_query($koneksi, "SELECT * FROM guru WHERE user_id = '$user_id'");
    $profil = mysqli_fetch_assoc($q) ?? [];
}

// Update Profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    
    if ($role == 'siswa') {
        $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
        mysqli_query($koneksi, "UPDATE siswa SET nama='$nama', kelas='$kelas' WHERE user_id='$user_id'");
    } elseif ($role == 'guru') {
        mysqli_query($koneksi, "UPDATE guru SET nama='$nama' WHERE user_id='$user_id'");
    }

    echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='profile.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4 mb-5">
    <div class="card shadow col-md-6 mx-auto">
        <div class="card-header bg-primary text-white text-center">
            <h5>Profil Saya (<?= ucfirst($role); ?>)</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($username); ?>" disabled>
                </div>

                <?php if ($role != 'admin'): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($profil['nama'] ?? ''); ?>" required>
                </div>
                <?php endif; ?>

                <?php if ($role == 'guru'): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">NIP</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($profil['nip'] ?? ''); ?>" disabled>
                </div>
                <?php endif; ?>

                <?php if ($role == 'siswa'): ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">NIS</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($profil['nis'] ?? ''); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kelas</label>
                    <input type="text" name="kelas" class="form-control" value="<?= htmlspecialchars($profil['kelas'] ?? ''); ?>" required>
                </div>
                <?php endif; ?>

                <?php if ($role != 'admin'): ?>
                <button type="submit" class="btn btn-success w-100">Simpan Perubahan Profil</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
</body>
</html>