<?php
error_reporting(0);
ini_set('display_errors', 0);

require 'navbar.php';
require_once 'koneksi.php';

$role = strtolower($_SESSION['role'] ?? '');
$user_id = $_SESSION['user_id'] ?? '';
$username_login = $_SESSION['username'] ?? '';

// Query data nilai dengan mengambil seluruh data tabel nilai agar pasti tampil
if ($role === 'siswa') {
    $query = mysqli_query($koneksi, "SELECT * FROM nilai WHERE nama_siswa = '$username_login' OR nis = '$username_login'");
} else {
    // Admin dan Guru menampilkan semua rekap data nilai yang ada di database
    $query = mysqli_query($koneksi, "SELECT * FROM nilai");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nilai Siswa - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-custom { 
            border-radius: 16px; 
            border: none; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
        }
        .table-custom th { 
            background-color: #0d6efd !important; 
            color: white !important; 
            text-transform: uppercase; 
            font-size: 0.82rem; 
            letter-spacing: 0.5px;
            padding: 12px 10px;
        }
        .table-custom td {
            padding: 12px 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body class="bg-light pb-5">

<div class="container my-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Daftar Nilai Siswa</h2>
            <p class="text-muted mb-0">Kelola dan pantau hasil belajar siswa dengan sistem terpadu.</p>
        </div>
        <?php if ($role === 'admin' || $role === 'guru'): ?>
            <a href="tambah_nilai.php" class="btn btn-primary fw-semibold shadow-sm px-4 py-2">
                <i class="bi bi-plus-circle me-1"></i> Input Nilai Baru
            </a>
        <?php endif; ?>
    </div>

    <div class="card card-custom">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle table-custom">
                    <thead class="text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Nama Siswa</th>
                            <th width="15%">NIS</th>
                            <th width="12%">Pengetahuan</th>
                            <th width="12%">Keterampilan</th>
                            <?php if ($role === 'admin' || $role === 'guru'): ?>
                                <th width="12%">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($query && mysqli_num_rows($query) > 0):
                            while ($row = mysqli_fetch_assoc($query)):
                        ?>
                            <tr>
                                <td class="text-center fw-bold text-secondary"><?= $no++; ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?></td>
                                <td class="text-center text-muted"><?= htmlspecialchars($row['nis'] ?? '-'); ?></td>
                                <td class="text-center fw-bold text-primary bg-light"><?= htmlspecialchars($row['nilai_pengetahuan'] ?? '0'); ?></td>
                                <td class="text-center fw-bold text-success bg-light"><?= htmlspecialchars($row['nilai_keterampilan'] ?? '0'); ?></td>
                                
                                <?php if ($role === 'admin' || $role === 'guru'): ?>
                                    <td class="text-center">
                                        <a href="edit_nilai.php?id=<?= $row['id'] ?? $row['id_nilai'] ?? ''; ?>" class="btn btn-warning btn-sm text-white fw-bold px-2 py-1 me-1 shadow-sm" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus_nilai.php?id=<?= $row['id'] ?? $row['id_nilai'] ?? ''; ?>" class="btn btn-danger btn-sm fw-bold px-2 py-1 shadow-sm" onclick="return confirm('Yakin ingin menghapus data nilai ini?');" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="<?= ($role === 'admin' || $role === 'guru') ? '6' : '5'; ?>" class="text-center py-5 text-muted fst-italic bg-white">
                                    <i class="bi bi-folder2-open display-6 d-block mb-2 text-secondary"></i>
                                    Belum ada data nilai yang tersimpan di dalam database. Silakan lakukan input nilai terlebih dahulu.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>