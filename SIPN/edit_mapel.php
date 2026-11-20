<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi admin
if (!isset($_SESSION['login']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? mysqli_real_escape_string($koneksi, trim($_GET['id'])) : '';

if (empty($id)) {
    echo "<script>alert('ID Mata Pelajaran tidak valid!'); window.location.href='mapel.php';</script>";
    exit;
}

// Ambil data mapel berdasarkan ID (sesuaikan 'id' jika di tabel mapel bernama 'ID' atau 'id_mapel')
$query = mysqli_query($koneksi, "SELECT * FROM mapel WHERE id = '$id' LIMIT 1");
if (!$query) {
    die("Query error: " . mysqli_error($koneksi));
}
$mapel = mysqli_fetch_assoc($query);

if (!$mapel) {
    echo "<script>alert('Data mata pelajaran tidak ditemukan!'); window.location.href='mapel.php';</script>";
    exit;
}

$id_mapel   = $mapel['id'] ?? '';
$nama_mapel = $mapel['nama_mapel'] ?? ($mapel['mapel'] ?? '');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mata Pelajaran - SIPN Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Mata Pelajaran</h5>
                </div>
                <div class="card-body p-4">

                    <form action="aksi_edit_mapel.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($id_mapel); ?>">

                        <div class="mb-3">
                            <label for="nama_mapel" class="form-label fw-semibold">Nama Mata Pelajaran</label>
                            <input type="text" class="form-control" id="nama_mapel" name="nama_mapel" value="<?= htmlspecialchars($nama_mapel); ?>" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="mapel.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" name="update" class="btn btn-dark btn-sm px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>