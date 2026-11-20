<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login']) || strtolower(trim((string)($_SESSION['role'] ?? ''))) !== 'admin') {
    header("Location: login.php");
    exit;
}

function ambil_kolom_dg(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kolom_guru  = ambil_kolom_dg($koneksi, 'guru');
$kolom_users = ambil_kolom_dg($koneksi, 'users');

$pk_guru  = in_array('id', $kolom_guru, true) ? 'id' : (in_array('ID', $kolom_guru, true) ? 'ID' : 'id');
$pk_users = in_array('id', $kolom_users, true) ? 'id' : (in_array('ID', $kolom_users, true) ? 'ID' : 'id');

$join_sql = "";
if (!empty($kolom_users) && in_array('user_id', $kolom_guru, true)) {
    $join_sql .= " LEFT JOIN users u ON g.user_id = u.`$pk_users` ";
}

$select_username = (!empty($kolom_users) && in_array('username', $kolom_users, true) && in_array('user_id', $kolom_guru, true))
    ? "COALESCE(NULLIF(TRIM(u.username), ''), '-')"
    : "'-'";

$select_nama = in_array('nama', $kolom_guru, true)
    ? "COALESCE(NULLIF(TRIM(g.nama), ''), $select_username)"
    : $select_username;

$select_nip = in_array('nip', $kolom_guru, true)
    ? "COALESCE(NULLIF(TRIM(g.nip), ''), '-')"
    : "'-'";

$select_mapel = in_array('mapel', $kolom_guru, true)
    ? "COALESCE(NULLIF(TRIM(g.mapel), ''), '-')"
    : "'-'";

$sql_guru = "
    SELECT
        g.*,
        $select_nama AS nama_tampil,
        $select_nip AS nip_tampil,
        $select_mapel AS mapel_tampil,
        $select_username AS username_tampil
    FROM guru g
    $join_sql
    ORDER BY g.`$pk_guru` DESC
";

$res_guru = mysqli_query($koneksi, $sql_guru);
$rows_guru = [];
if ($res_guru) {
    while ($r = mysqli_fetch_assoc($res_guru)) {
        $rows_guru[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Guru - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php if (file_exists('navbar.php')) { include 'navbar.php'; } ?>

<div class="container my-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-person-lines-fill me-2"></i>Daftar Guru</h4>
                <small class="text-muted">Kelola data guru dan pengajar yang terdaftar di sistem SIPN.</small>
            </div>
            <a href="tambah_guru.php" class="btn btn-dark btn-sm fw-semibold">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Guru
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle border">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>NIP / Kode</th>
                            <th>Nama Lengkap & Gelar</th>
                            <th>Mata Pelajaran</th>
                            <th>Username Login</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rows_guru)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($rows_guru as $g): ?>
                                <?php $id_g = (string)($g[$pk_guru] ?? ''); ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars((string)($g['nip_tampil'] ?? '-')); ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars((string)($g['nama_tampil'] ?? '-')); ?></td>
                                    <td><?= htmlspecialchars((string)($g['mapel_tampil'] ?? '-')); ?></td>
                                    <td><?= htmlspecialchars((string)($g['username_tampil'] ?? '-')); ?></td>
                                    <td>
                                        <a href="edit_guru.php?id=<?= urlencode($id_g); ?>" class="btn btn-warning btn-sm fw-semibold">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="hapus_guru.php?id=<?= urlencode($id_g); ?>" class="btn btn-danger btn-sm fw-semibold" onclick="return confirm('Yakin ingin menghapus data guru ini?');">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data guru.</td>
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