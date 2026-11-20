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

// Ambil data users dari database menggunakan kolom 'nama'
$sql = "SELECT id, username, nama, role FROM users ORDER BY id DESC";
$result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-people-fill me-2"></i>Daftar Pengguna Sistem</h2>
            <p class="text-muted mb-0">Kelola data akun pengguna SIPN.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 8%;">No</th>
                        <th style="width: 30%;">Username</th>
                        <th style="width: 30%;">Nama</th>
                        <th style="width: 17%;">Role</th>
                        <th class="text-center" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $ada_data = false;
                    
                    if ($result) {
                        while ($row = mysqli_fetch_array($result)) {
                            $ada_data = true;
                    ?>
                            <tr>
                                <td class="fw-semibold"><?php echo $no++; ?></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['nama'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    $role = strtolower($row['role'] ?? '');
                                    $badge_color = ($role === 'admin') ? 'bg-danger' : (($role === 'guru') ? 'bg-primary' : 'bg-success');
                                    ?>
                                    <span class="badge <?php echo $badge_color; ?> text-uppercase px-2 py-1"><?php echo htmlspecialchars($row['role'] ?? ''); ?></span>
                                </td>
                               <td class="text-center">
    <!-- Pastikan href mengarah ke 'edit_user.php' dan membawa '?id=' -->
    <a href="aksi_edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
    
    <a href="aksi_hapus_user.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger fw-semibold px-2" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">Hapus</a>
</td>
                            </tr>
                    <?php 
                        }
                    } 
                    
                    if (!$ada_data) {
                    ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Belum ada data pengguna yang tersedia.
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>