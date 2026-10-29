<?php
require_once 'navbar.php';
require_once 'koneksi.php';

// Ambil data mata pelajaran untuk dropdown
$query_mapel = "SELECT * FROM mapel ORDER BY nama_mapel ASC";
$result_mapel = mysqli_query($koneksi, $query_mapel);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Nilai Raport - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5" style="max-width: 600px;">
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white py-3">
            <h5 class="mb-0 fw-bold">Form Input Nilai Raport</h5>
        </div>
        <div class="card-body p-4">
            
            <form action="tambah_nilai.php" method="POST">
                
                <!-- Pilih Mata Pelajaran -->
                <div class="mb-3">
                    <label for="mapel_id" class="form-label fw-bold">Mata Pelajaran</label>
                    <select name="mapel_id" id="mapel_id" class="form-select" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php while ($row = mysqli_fetch_assoc($result_mapel)): ?>
                            <option value="<?= $row['ID']; ?>">
                                <?= htmlspecialchars($row['kode_mapel']); ?> - <?= htmlspecialchars($row['nama_mapel']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Nilai KKM -->
                <div class="mb-3">
                    <label for="nilai_kkm" class="form-label fw-bold">Nilai KKM</label>
                    <input type="number" name="nilai_kkm" id="nilai_kkm" class="form-control" placeholder="Contoh: 75" required>
                </div>

                <!-- Nilai Pengetahuan -->
                <div class="mb-3">
                    <label for="nilai_pengetauan" class="form-label fw-bold">Nilai Pengetahuan</label>
                    <input type="number" name="nilai_pengetauan" id="nilai_pengetauan" class="form-control" placeholder="Contoh: 85" required>
                </div>

                <!-- Nilai Keterampilan -->
                <div class="mb-3">
                    <label for="nilai_keterampilan" class="form-label fw-bold">Nilai Keterampilan</label>
                    <input type="number" name="nilai_keterampilan" id="nilai_keterampilan" class="form-control" placeholder="Contoh: 88" required>
                </div>

                <!-- Keterangan -->
                <div class="mb-4">
                    <label for="keterangan" class="form-label fw-bold">Keterangan</label>
                    <select name="keterangan" id="keterangan" class="form-select" required>
                        <option value="Tuntas">Tuntas</option>
                        <option value="Belum Tuntas">Belum Tuntas</option>
                    </select>
                </div>

                <!-- Tombol Submit -->
                <div class="d-flex justify-content-between">
                    <a href="menampilkan_data.php" class="btn btn-secondary fw-bold">Kembali</a>
                    <button type="submit" name="simpan" class="btn btn-primary fw-bold">Simpan Nilai</button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>