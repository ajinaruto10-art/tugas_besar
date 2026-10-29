<?php
require_once 'navbar.php';
require_once 'koneksi.php';

if ($_SESSION['role'] != 'guru') {
    echo "<script>alert('Akses khusus Guru!'); window.location.href='dshaboard.php';</script>";
    exit;
}

// Proses Simpan / Update Nilai
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $siswa_id           = $_POST['siswa_id'];
    $mapel_id           = $_POST['mapel_id'];
    $nilai_kkm          = $_POST['nilai_kkm'];
    $nilai_pengetahuan  = $_POST['nilai_pengetahuan'];
    $nilai_keterampilan = $_POST['nilai_keterampilan'];
    
    // Tentukan Keterangan tuntas/tidak tuntas
    $rata_rata = ($nilai_pengetahuan + $nilai_keterampilan) / 2;
    $keterangan = ($rata_rata >= $nilai_kkm) ? 'Tuntas' : 'Tidak Tuntas';

    // Cek apakah nilai sudah pernah diinput
    $cek = mysqli_query($koneksi, "SELECT * FROM nilai_rapor WHERE siswa_id='$siswa_id' AND mapel_id='$mapel_id'");
    if (mysqli_num_rows($cek) > 0) {
        $query = "UPDATE nilai_rapor SET nilai_kkm='$nilai_kkm', nilai_pengetahuan='$nilai_pengetahuan', nilai_keterampilan='$nilai_keterampilan', Keterangan='$keterangan' WHERE siswa_id='$siswa_id' AND mapel_id='$mapel_id'";
    } else {
        $query = "INSERT INTO nilai_rapor (siswa_id, mapel_id, nilai_kkm, nilai_pengetahuan, nilai_keterampilan, Keterangan) VALUES ('$siswa_id', '$mapel_id', '$nilai_kkm', '$nilai_pengetahuan', '$nilai_keterampilan', '$keterangan')";
    }
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Nilai berhasil disimpan!'); window.location.href='kelola_nilai.php';</script>";
    }
}

$siswa_list = mysqli_query($koneksi, "SELECT * FROM siswa ORDER BY nama ASC");
$mapel_list = mysqli_query($koneksi, "SELECT * FROM mapel ORDER BY nama_mapel ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Nilai - Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="card shadow col-md-8 mx-auto">
        <div class="card-header bg-success text-white">
            <h5>Form Input / Edit Nilai Rapor Siswa</h5>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Pilih Siswa</label>
                    <select name="siswa_id" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php while ($s = mysqli_fetch_assoc($siswa_list)): ?>
                            <option value="<?= $s['id']; ?>"><?= htmlspecialchars($s['nama']); ?> (<?= $s['kelas']; ?>)</option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mata Pelajaran</label>
                    <select name="mapel_id" class="form-select" required>
                        <option value="">-- Pilih Mapel --</option>
                        <?php while ($m = mysqli_fetch_assoc($mapel_list)): ?>
                            <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['nama_mapel']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nilai KKM</label>
                        <input type="number" name="nilai_kkm" class="form-control" value="75" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nilai Pengetahuan</label>
                        <input type="number" name="nilai_pengetahuan" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nilai Keterampilan</label>
                        <input type="number" name="nilai_keterampilan" class="form-control" min="0" max="100" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100">Simpan Nilai</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>