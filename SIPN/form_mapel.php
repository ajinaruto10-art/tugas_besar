<?php
require_once 'navbar.php';
require_once 'koneksi.php';

// Proteksi Akses Khusus Admin
$user_role = strtolower($_SESSION['role'] ?? '');
if ($user_role !== 'admin') {
    echo "<script>alert('Akses ditolak! Khusus Admin.'); window.location.href='dhasboard.php';</script>";
    exit;
}

$msg = "";
$msg_type = "";

// Proses Simpan Data Mapel
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_mapel = mysqli_real_escape_string($koneksi, $_POST['kode_mapel']);
    $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);
    $guru_id    = !empty($_POST['guru_id']) ? "'".$_POST['guru_id']."'" : "NULL";

    // Cek Kode Mapel agar tidak ganda
    $cek = mysqli_query($koneksi, "SELECT * FROM mapel WHERE kode_mapel = '$kode_mapel'");
    if (mysqli_num_rows($cek) > 0) {
        $msg = "Kode Mata Pelajaran sudah ada! Gunakan kode lain.";
        $msg_type = "danger";
    } else {
        $query = "INSERT INTO mapel (kode_mapel, nama_mapel, guru_id) VALUES ('$kode_mapel', '$nama_mapel', $guru_id)";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Mata Pelajaran Berhasil Ditambahkan!'); window.location.href='mapel.php';</script>";
            exit;
        } else {
            $msg = "Gagal menyimpan data: " . mysqli_error($koneksi);
            $msg_type = "danger";
        }
    }
}

// Ambil Data Guru untuk Dropdown Pengampu
$q_guru = mysqli_query($koneksi, "SELECT * FROM guru ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Mata Pelajaran - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow border-0 col-md-6 mx-auto">
        <div class="card-header bg-primary text-white py-3 text-center">
            <h5 class="mb-0 fw-bold">Form Tambah Mata Pelajaran</h5>
        </div>
        <div class="card-body p-4">

            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?= $msg_type; ?> alert-dismissible fade show" role="alert">
                    <?= $msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Kode Mata Pelajaran</label>
                    <input type="text" name="kode_mapel" class="form-control" placeholder="Contoh: MP001" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" class="form-control" placeholder="Contoh: Pemrograman Web" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Guru Pengampu</label>
                    <select name="guru_id" class="form-select">
                        <option value="">-- Pilih Guru Pengampu (Opsional) --</option>
                        <?php if ($q_guru && mysqli_num_rows($q_guru) > 0): ?>
                            <?php while($g = mysqli_fetch_assoc($q_guru)): ?>
                                <option value="<?= $g['id']; ?>"><?= htmlspecialchars($g['nama']); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="mapel.php" class="btn btn-secondary">← Kembali ke Tabel Mapel</a>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan Mapel Baru</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>