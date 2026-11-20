<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek hak akses admin
if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil ID dari URL (ID siswa)
$id_siswa = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (empty($id_siswa)) {
    echo "<script>alert('ID Siswa tidak valid!'); window.location.href='tabel_siswa.php';</script>";
    exit;
}

// Ambil data siswa beserta data akun users-nya menggunakan JOIN
$sql = "SELECT siswa.*, users.username, users.jenis_kelamin, users.password, users.role 
        FROM siswa 
        JOIN users ON siswa.user_id = users.id 
        WHERE siswa.ID = '$id_siswa' LIMIT 1";
$result = mysqli_query($koneksi, $sql);
$siswa = mysqli_fetch_array($result);

if (!$siswa) {
    echo "<script>alert('Data siswa tidak ditemukan di database!'); window.location.href='tabel_siswa.php';</script>";
    exit;
}

$user_id = $siswa['user_id'];

// Proses jika tombol Simpan Perubahan diklik
if (isset($_POST['update_siswa'])) {
    $nis           = mysqli_real_escape_string($koneksi, trim($_POST['nis']));
    $nama          = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $kelas         = mysqli_real_escape_string($koneksi, trim($_POST['kelas']));
    $username      = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $jenis_kelamin = mysqli_real_escape_string($koneksi, trim($_POST['jenis_kelamin']));
    $password      = mysqli_real_escape_string($koneksi, trim($_POST['password']));

    if (empty($nis) || empty($nama) || empty($username)) {
        echo "<script>alert('NIS, Nama, dan Username wajib diisi!'); window.history.back();</script>";
        exit;
    }

    // 1. Update data di tabel siswa (nama, nis, kelas)
    $update_siswa = mysqli_query($koneksi, "UPDATE siswa SET nama = '$nama', nis = '$nis', kelas = '$kelas' WHERE ID = '$id_siswa'");

    // 2. Update data di tabel users (username, nama, jenis_kelamin, dan password jika diisi)
    if (!empty($password)) {
        $query_users = "UPDATE users SET username = '$username', nama = '$nama', jenis_kelamin = '$jenis_kelamin', password = '$password' WHERE id = '$user_id'";
    } else {
        $query_users = "UPDATE users SET username = '$username', nama = '$nama', jenis_kelamin = '$jenis_kelamin' WHERE id = '$user_id'";
    }
    $update_users = mysqli_query($koneksi, $query_users);

    if ($update_siswa && $update_users) {
        echo "<script>alert('Data siswa dan akun pengguna berhasil diperbarui serta disinkronkan!'); window.location.href='tabel_siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 p-4">
                <h3 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Data Lengkap Siswa</h3>
                <p class="text-muted mb-4">Perbarui informasi akun dan data akademik siswa. Perubahan nama akan otomatis disinkronkan.</p>

                <form action="" method="POST">
                    <div class="row">
                        <!-- Kolom Kiri: Data Akun Users -->
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label fw-semibold">Username Login</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($siswa['username'] ?? ''); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label fw-semibold">Password <span class="text-muted small fw-normal">(Kosongkan jika tetap)</span></label>
                            <input type="text" class="form-control" id="password" name="password" placeholder="Masukkan password baru...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($siswa['nama'] ?? ''); ?>" required>
                        <div class="form-text text-muted">Perubahan nama di sini akan otomatis merembes memperbarui tabel users.</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nis" class="form-label fw-semibold">NIS (Nomor Induk Siswa)</label>
                            <input type="text" class="form-control" id="nis" name="nis" value="<?php echo htmlspecialchars($siswa['nis'] ?? ''); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kelas" class="form-label fw-semibold">Kelas</label>
                            <input type="text" class="form-control" id="kelas" name="kelas" value="<?php echo htmlspecialchars($siswa['kelas'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L" <?php if (($siswa['jenis_kelamin'] ?? '') === 'L') echo 'selected'; ?>>Laki-laki (L)</option>
                            <option value="P" <?php if (($siswa['jenis_kelamin'] ?? '') === 'P') echo 'selected'; ?>>Perempuan (P)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="tabel_siswa.php" class="btn btn-secondary fw-semibold px-4">Kembali</a>
                        <button type="submit" name="update_siswa" class="btn btn-dark fw-semibold px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 