<?php
require_once __DIR__ . '/koneksi.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: mapel.php");
    exit();
}

$id = mysqli_real_escape_string($koneksi, $id);

// Ambil data mapel berdasarkan ID
$query_mapel = mysqli_query($koneksi, "SELECT * FROM mapel WHERE id = '$id'");
$data_mapel = mysqli_fetch_assoc($query_mapel);

if (!$data_mapel) {
    header("Location: index.php");
    exit();
}

// Proses update data saat form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_mapel = mysqli_real_escape_string($koneksi, $_POST['kode_mapel']);
    $nama_mapel = mysqli_real_escape_string($koneksi, $_POST['nama_mapel']);
    $guru_id    = !empty($_POST['guru_id']) ? "'".mysqli_real_escape_string($koneksi, $_POST['guru_id'])."'" : "NULL";

    $update = "UPDATE mapel SET 
                kode_mapel = '$kode_mapel', 
                nama_mapel = '$nama_mapel', 
                guru_id = $guru_id 
               WHERE id = '$id'";

    if (mysqli_query($koneksi, $update)) {
        header("Location: mapel.php?status=success_edit");
        exit();
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}

// Ambil list guru untuk dropdown
$guru_query = mysqli_query($koneksi, "SELECT * FROM guru ORDER BY nama ASC");

require_once __DIR__ . '/navbar.php';
?>

<div class="container mt-4 mb-5">
    <div class="card shadow-sm col-md-8 mx-auto">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Edit Mata Pelajaran</h4>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Kode Mapel</label>
                    <input type="text" name="kode_mapel" class="form-control" value="<?= htmlspecialchars($data_mapel['kode_mapel']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Mapel</label>
                    <input type="text" name="nama_mapel" class="form-control" value="<?= htmlspecialchars($data_mapel['nama_mapel']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Guru </label>
                    <select name="guru_id" class="form-select">
                        <option value="">-- Pilih Guru (Opsional) --</option>
                        <?php while ($guru = mysqli_fetch_assoc($guru_query)): ?>
                            <option value="<?= $guru['id']; ?>" <?= ($guru['id'] == $data_mapel['guru_id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($guru['nama']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>