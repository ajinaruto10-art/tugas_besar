<?php
require_once __DIR__ . '/koneksi.php';

// 1. Proses simpan data ketika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_mapel = trim($_POST['kode_mapel'] ?? '');
    $nama_mapel = trim($_POST['nama_mapel'] ?? '');
    $guru_id    = !empty($_POST['guru_id']) ? "'".mysqli_real_escape_string($koneksi, $_POST['guru_id'])."'" : "NULL";

    if (!empty($kode_mapel) && !empty($nama_mapel)) {
        $kode_mapel = mysqli_real_escape_string($koneksi, $kode_mapel);
        $nama_mapel = mysqli_real_escape_string($koneksi, $nama_mapel);

        $query = "INSERT INTO mapel (kode_mapel, nama_mapel, guru_id) VALUES ('$kode_mapel', '$nama_mapel', $guru_id)";

        if (mysqli_query($koneksi, $query)) {
            header("Location: mapel.php?status=success_tambah");
            exit();
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    } else {
        $error = "Kode Mapel dan Nama Mapel wajib diisi!";
    }
}

// 2. Ambil data guru untuk opsi dropdown
$query_guru = mysqli_query($koneksi, "SELECT id, nama FROM guru ORDER BY nama ASC");

require_once __DIR__ . '/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Tambah Mata Pelajaran Baru</h5>
                </div>
                <div class="card-body p-4">

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="kode_mapel" class="form-label fw-bold">Kode Mapel <span class="text-danger">*</span></label>
                            <input type="text" name="kode_mapel" id="kode_mapel" class="form-control" placeholder="Contoh: MP-001" required value="<?= htmlspecialchars($_POST['kode_mapel'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="nama_mapel" class="form-label fw-bold">Nama Mapel <span class="text-danger">*</span></label>
                            <input type="text" name="nama_mapel" id="nama_mapel" class="form-control" placeholder="Contoh: Pemrograman Web" required value="<?= htmlspecialchars($_POST['nama_mapel'] ?? ''); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="guru_id" class="form-label fw-bold">Guru Pengampu</label>
                            <select name="guru_id" id="guru_id" class="form-select">
                                <option value="">-- Pilih Guru (Opsional) --</option>
                                <?php if ($query_guru && mysqli_num_rows($query_guru) > 0): ?>
                                    <?php while ($guru = mysqli_fetch_assoc($query_guru)): ?>
                                        <option value="<?= $guru['id']; ?>" <?= (isset($_POST['guru_id']) && $_POST['guru_id'] == $guru['id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($guru['nama']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between border-top pt-3">
                            <a href="mapel.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary px-4">Simpan Mapel</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>