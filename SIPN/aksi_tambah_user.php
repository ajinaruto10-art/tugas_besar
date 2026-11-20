<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, $_GET['id']) : '';

if (empty($id)) {
    header("Location: daftar_user2.php");
    exit;
}

if (isset($_POST['update'])) {
    $username      = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $nama          = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $jenis_kelamin = mysqli_real_escape_string($koneksi, trim($_POST['jenis_kelamin']));
    $password      = mysqli_real_escape_string($koneksi, trim($_POST['password']));
    $role          = mysqli_real_escape_string($koneksi, trim($_POST['role']));

    if (empty($username) || empty($nama) || empty($role)) {
        echo "<script>alert('Username, Nama, dan Role wajib diisi!'); window.history.back();</script>";
        exit;
    }

    if (!empty($password)) {
        $query_update = "UPDATE users SET username = '$username', nama = '$nama', jenis_kelamin = '$jenis_kelamin', password = '$password', role = '$role' WHERE id = '$id'";
    } else {
        $query_update = "UPDATE users SET username = '$username', nama = '$nama', jenis_kelamin = '$jenis_kelamin', role = '$role' WHERE id = '$id'";
    }

    $query = mysqli_query($koneksi, $query_update);

    if ($query) {
        echo "<script>alert('Data pengguna berhasil diperbarui!'); window.location.href='daftar_user2.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
}

$sql = "SELECT * FROM users WHERE id = '$id' LIMIT 1";
$result = mysqli_query($koneksi, $sql);
$user = mysqli_fetch_array($result);

if (!$user) {
    echo "<script>alert('Data pengguna tidak ditemukan!'); window.location.href='daftar_user2.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 p-4">
                <h3 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2"></i>Edit Pengguna</h3>
                <p class="text-muted mb-4">Perbarui informasi akun pengguna sistem SIPN.</p>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label fw-semibold">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label fw-semibold">Jenis Kelamin</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L" <?php if (isset($user['jenis_kelamin']) && $user['jenis_kelamin'] === 'L') echo 'selected'; ?>>Laki-laki (L)</option>
                            <option value="P" <?php if (isset($user['jenis_kelamin']) && $user['jenis_kelamin'] === 'P') echo 'selected'; ?>>Perempuan (P)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Sandi / Password <span class="text-muted fw-normal small">(Kosongkan jika tidak ingin diubah)</span></label>
                        <input type="text" class="form-control" id="password" name="password" placeholder="Masukkan sandi baru...">
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label fw-semibold">Role / Hak Akses</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Admin</option>
                            <option value="guru" <?php if ($user['role'] === 'guru') echo 'selected'; ?>>Guru</option>
                            <option value="siswa" <?php if ($user['role'] === 'siswa') echo 'selected'; ?>>Siswa</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="daftar_user2.php" class="btn btn-secondary fw-semibold px-4">Kembali</a>
                        <button type="submit" name="update" class="btn btn-dark fw-semibold px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>