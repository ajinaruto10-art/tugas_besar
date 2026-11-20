<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi hanya admin atau guru yang boleh mengakses
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));

// Ambil data siswa dari database (bisa digabung dengan tabel users lewat uer_id jika relasinya ada)
$query_siswa = mysqli_query($koneksi, "SELECT siswa.*, users.username FROM siswa LEFT JOIN users ON siswa.user_id = users.id ORDER BY siswa.id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabel Seluruh Siswa - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Panggil Navbar Pusat -->
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-people-fill me-2"></i>Daftar Seluruh Siswa</h2>
            <p class="text-muted mb-0">Data lengkap siswa yang terdaftar di sistem SIPN.</p>
        </div>
        <?php if ($role === 'admin'): ?>
            <a href="tambah_siswa.php" class="btn btn-dark fw-semibold">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Siswa
            </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="py-3 ps-3" style="width: 5%;">No</th>
                            <th class="py-3">NIS</th>
                            <th class="py-3">Nama Lengkap</th>
                            <th class="py-3">Kelas</th>
                            <th class="py-3">Username Login</th>
                            <?php if ($role === 'admin'): ?>
                                <th class="py-3 text-center" style="width: 15%;">Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query_siswa && mysqli_num_rows($query_siswa) > 0): ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($query_siswa)): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nis']); ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kelas']); ?></span></td>
                                    <td><?= htmlspecialchars($row['username'] ?? '-'); ?></td>
                                    
                                    <?php if ($role === 'admin'): ?>
                                        <td class="text-center">
                                            <a href="aksi_edit_siswa.php?id=<?= $row['ID']; ?>" class="btn btn-sm btn-warning text-dark fw-semibold px-2 me-1">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <a href="aksi_hapus_siswa.php?id=<?= $row['ID']; ?>" class="btn btn-sm btn-danger fw-semibold px-2" onclick="return confirm('Yakin ingin menghapus data siswa ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= ($role === 'admin') ? 6 : 5; ?>" class="text-center py-4 text-muted">
                                    Belum ada data siswa yang terdaftar.
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