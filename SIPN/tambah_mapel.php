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

// Ambil daftar guru untuk dropdown Pilih Guru
$query_guru = mysqli_query($koneksi, "SELECT * FROM guru ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tambah Mata Pelajaran - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-4">
    <div class="card shadow-sm border">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-semibold">Form Tambah Mata Pelajaran</h5>
        </div>
        <div class="card-body p-4">
            <h3 class="fw-bold mb-4">Mata Pelajaran</h3>

            <form action="aksi_tambah_mapel.php" method="POST">
                <div class="mb-3">
                    <label for="kode_mapel" class="form-label">Kode Mata Pelajaran</label>
                    <input type="text" class="form-control" id="kode_mapel" name="kode_mapel" placeholder="Kode Mapel | Contoh: MP001" required>
                </div>

                <div class="mb-3">
                    <label for="nama_mapel" class="form-label">Nama Mata Pelajaran</label>
                    <input type="text" class="form-control" id="nama_mapel" name="nama_mapel" placeholder="Nama Mata Pelajaran | Contoh: Pemrograman Web" required>
                </div>

                <div class="mb-4">
                    <label for="guru_id" class="form-label">Pilih Guru</label>
                    <select class="form-select" id="guru_id" name="guru_id">
                        <option value="">Pilih Guru...</option>
                        <?php if ($query_guru && mysqli_num_rows($query_guru) > 0): ?>
                            <?php while ($g = mysqli_fetch_assoc($query_guru)): ?>
                                <?php
                                $id_guru   = $g['id'] ?? ($g['ID'] ?? '');
                                $nama_guru = $g['nama'] ?? ($g['nama_guru'] ?? '');
                                $mapel_g   = $g['mapel'] ?? '';
                                ?>
                                <option value="<?= htmlspecialchars($id_guru); ?>" data-nama-guru="<?= htmlspecialchars($nama_guru); ?>">
                                    <?= htmlspecialchars($nama_guru); ?><?= !empty($mapel_g) ? ' (' . htmlspecialchars($mapel_g) . ')' : ''; ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="simpan_mapel" class="btn btn-primary fw-semibold px-3">Simpan Data</button>
                    <a href="mapel.php" class="btn btn-secondary fw-semibold px-3">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>