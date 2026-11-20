<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Memanggil koneksi database
include 'koneksi.php';

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

// Mengambil data mapel beserta nama guru langsung menggunakan query native (tanpa function)
$sql = "SELECT mapel.*, guru.nama AS nama_guru FROM mapel LEFT JOIN guru ON mapel.id_guru = guru.id ORDER BY mapel.id DESC";
$result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mata Pelajaran - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Panggil Navbar Pusat -->
<?php include 'navbar.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-book-fill me-2"></i>Daftar Mata Pelajaran</h2>
            <p class="text-muted mb-0">Kelola data mata pelajaran dan guru pengampu di sistem SIPN.</p>
        </div>
        <?php if ($role === 'admin'): ?>
            <a href="tambah_mapel.php" class="btn btn-dark fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> Tambah Mata Pelajaran
            </a>
        <?php endif; ?>
    </div>

    <div class="card shadow-sm border-0 p-4">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">Kode Mapel</th>
                        <th style="width: 35%;">Nama Mata Pelajaran</th>
                        <th style="width: 25%;">Guru Pengampu</th>
                        <?php if ($role === 'admin'): ?>
                            <th class="text-center" style="width: 15%;">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $ada_data = false;
                    
                    // Melakukan perulangan data secara native langsung dari hasil query
                    if ($result) {
                        while ($row = mysqli_fetch_array($result)) {
                            $ada_data = true;
                    ?>
                            <tr>
                                <td class="fw-semibold"><?php echo $no++; ?></td>
                                <td><span class="badge bg-secondary"><?php echo isset($row['kode_mapel']) ? htmlspecialchars($row['kode_mapel']) : '-'; ?></span></td>
                                <td class="fw-semibold"><?php echo isset($row['nama_mapel']) ? htmlspecialchars($row['nama_mapel']) : '-'; ?></td>
                                <td><?php echo isset($row['nama_guru']) && $row['nama_guru'] != '' ? htmlspecialchars($row['nama_guru']) : 'Belum ditentukan'; ?></td>
                                
                                <?php if ($role === 'admin'): ?>
                                    <td class="text-center">
                                        <a href="edit_mapel.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-warning text-dark fw-semibold px-2 me-1">Edit</a>
                                        <a href="hapus_mapel.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-danger fw-semibold px-2" onclick="return confirm('Yakin ingin menghapus mata pelajaran ini?')">Hapus</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                    <?php 
                        }
                    } 
                    
                    // Jika data kosong
                    if (!$ada_data) {
                    ?>
                        <tr>
                            <td colspan="<?php echo ($role === 'admin') ? 5 : 4; ?>" class="text-center py-4 text-muted">
                                Belum ada data mata pelajaran yang tersedia.
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