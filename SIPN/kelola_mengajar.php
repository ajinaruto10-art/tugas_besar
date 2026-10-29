<?php
require_once 'navbar.php';
require_once 'koneksi.php';

if ($_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses khusus Admin!'); window.location.href='dhasboard.php';</script>";
    exit;
}

// Prosess Tambah Plotting Mengajar
if (isset($_POST['tambah_mengajar'])) {
    $mapel_id = $_POST['mapel_id'];
    $siswa_id = $_POST['siswa_id'];

    $insert = mysqli_query($koneksi, "INSERT INTO mengajar (mapel_id, siswa_id) VALUES ('$mapel_id', '$siswa_id')");
    if ($insert) {
        echo "<script>alert('Data mengajar berhasil ditambahkan!'); window.location.href='kelola_mengajar.php';</script>";
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM mengajar WHERE id='$id'");
    header("Location: kelola_mengajar.php");
    exit;
}

$q_mengajar = mysqli_query($koneksi, "
    SELECT mg.id, m.nama_mapel, s.nama AS nama_siswa, s.kelas 
    FROM mengajar mg
    JOIN mapel m ON mg.mapel_id = m.id
    JOIN siswa s ON mg.siswa_id = s.id
");

$q_mapel = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY nama_mapel ASC");
$q_siswa = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Mengajar - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4 mb-5">
    <div class="row">
        <!-- Form Tambah -->
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Plotting Mengajar</h5>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select" required>
                                <option value="">-- Pilih Mapel --</option>
                                <?php while($m = mysqli_fetch_assoc($q_mapel)): ?>
                                    <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['nama_mapel']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Siswa</label>
                            <select name="siswa_id" class="form-select" required>
                                <option value="">-- Pilih Siswa --</option>
                                <?php while($s = mysqli_fetch_assoc($q_siswa)): ?>
                                    <option value="<?= $s['id']; ?>"><?= htmlspecialchars($s['nama']); ?> (<?= $s['kelas']; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="tambah_mengajar" class="btn btn-primary w-100">Simpan Data</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Daftar Relasi Mengajar</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>No</th>
                                <th>Mata Pelajaran</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; while($r = mysqli_fetch_assoc($q_mengajar)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($r['nama_mapel']); ?></td>
                                <td><?= htmlspecialchars($r['nama_siswa']); ?></td>
                                <td class="text-center"><?= htmlspecialchars($r['kelas']); ?></td>
                                <td class="text-center">
                                    <a href="kelola_mengajar.php?hapus=<?= $r['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>