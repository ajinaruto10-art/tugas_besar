<?php
require_once 'navbar.php';
require_once 'koneksi.php';

if ($_SESSION['role'] != 'siswa') {
    echo "<script>alert('Akses khusus Siswa!'); window.location.href='dhasboard.php';</script>";
    exit;
}

$user_id =$_SESSION['user_id'];

// Ambil data siswa berdasarkan user_id login
$q_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE user_id = '$user_id'");
$siswa = mysqli_fetch_assoc($q_siswa);
$siswa_id =$siswa['id'] ?? 0;

// Query mengambil nilai rapor dengan menyesuaikan kolom `id` di tabel nilai_rapor
$q_nilai = mysqli_query($koneksi, "
    SELECT n.*, m.kode_mapel, m.nama_mapel 
    FROM nilail_raport n 
    JOIN mapel m ON n.mapel_id = m.id 
    WHERE n.id = '$siswa_id'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Nilai Rapor Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Rapor Nilai - <?= htmlspecialchars($siswa['nama'] ?? $_SESSION['username']); ?> (Kelas: <?= htmlspecialchars($siswa['kelas'] ?? '-'); ?>)</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Kode Mapel</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>KKM</th>
                        <th>Nilai Pengetahuan</th>
                        <th>Nilai Keterampilan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    if ($q_nilai && mysqli_num_rows($q_nilai) > 0): 
                        while($r = mysqli_fetch_assoc($q_nilai)): 
                    ?>
                    <tr class="text-center">
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($r['kode_mapel']); ?></td>
                        <td class="text-start"><?= htmlspecialchars($r['nama_mapel']); ?></td>
                        <td><?= $r['nilai_kkm']; ?></td>
                        <td><?= $r['nilai_pengetahuan']; ?></td>
                        <td><?= $r['nilai_keterampilan']; ?></td>
                        <td>
                            <span class="badge <?= ($r['Keterangan'] == 'Tuntas') ? 'bg-success' : 'bg-danger'; ?>">
                                <?= htmlspecialchars($r['Keterangan']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr><td colspan="7" class="text-center text-muted">Belum ada nilai yang dimasukkan oleh guru.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>