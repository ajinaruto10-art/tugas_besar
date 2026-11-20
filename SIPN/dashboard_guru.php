<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
if ($role !== 'guru' && $role !== 'admin') {
    header("Location: login.php");
    exit;
}

function dg_kolom(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kol_guru  = dg_kolom($koneksi, 'guru');
$kol_users = dg_kolom($koneksi, 'users');

$pk_guru  = in_array('id', $kol_guru, true) ? 'id' : (in_array('ID', $kol_guru, true) ? 'ID' : 'id');
$pk_users = in_array('id', $kol_users, true) ? 'id' : (in_array('ID', $kol_users, true) ? 'ID' : 'id');

$join_users = "";
if (!empty($kol_users) && in_array('user_id', $kol_guru, true)) {
    $join_users = "LEFT JOIN `users` u ON g.`user_id` = u.`$pk_users`";
}

$select_username = (!empty($kol_users) && in_array('username', $kol_users, true) && in_array('user_id', $kol_guru, true))
    ? "COALESCE(NULLIF(TRIM(u.`username`), ''), '-')"
    : "'-'";

$select_nip = in_array('nip', $kol_guru, true)
    ? "COALESCE(NULLIF(TRIM(g.`nip`), ''), '-')"
    : "'-'";

$select_nama = in_array('nama', $kol_guru, true)
    ? "COALESCE(NULLIF(TRIM(g.`nama`), ''), $select_username)"
    : $select_username;

$select_mapel = in_array('mapel', $kol_guru, true)
    ? "COALESCE(NULLIF(TRIM(g.`mapel`), ''), '-')"
    : "'-'";

$query_guru = "
    SELECT
        g.*,
        $select_nip AS nip_tampil,
        $select_nama AS nama_tampil,
        $select_mapel AS mapel_tampil,
        $select_username AS username_tampil
    FROM `guru` g
    $join_users
    ORDER BY g.`$pk_guru` DESC
";

$res_guru = mysqli_query($koneksi, $query_guru);
$data_guru = [];
if ($res_guru) {
    while ($row = mysqli_fetch_assoc($res_guru)) {
        $data_guru[] = $row;
    }
}

$user_login_id = (string)($_SESSION['user_id'] ?? $_SESSION['id'] ?? '');
$username_login = (string)($_SESSION['username'] ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php if (file_exists('navbar.php')) { include 'navbar.php'; } ?>

<div class="container my-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-table me-2"></i>Data Guru
                </h4>
                <small class="text-muted">
                    Login sebagai: <strong><?= htmlspecialchars($username_login !== '' ? $username_login : 'Guru'); ?></strong>
                </small>
            </div>
            <?php if ($role === 'admin'): ?>
                <a href="tambah_guru.php" class="btn btn-dark btn-sm fw-semibold">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Guru
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>NIP</th>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Username</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data_guru)): ?>
                            <?php $no = 1; ?>
                            <?php foreach ($data_guru as $g): ?>
                                <?php
                                $is_self = false;
                                if ($user_login_id !== '' && isset($g['user_id']) && (string)$g['user_id'] === $user_login_id) {
                                    $is_self = true;
                                } elseif ($username_login !== '' && strtolower(trim((string)($g['username_tampil'] ?? ''))) === strtolower(trim($username_login))) {
                                    $is_self = true;
                                }
                                ?>
                                <tr class="<?= $is_self ? 'table-primary' : ''; ?>">
                                    <td class="fw-bold"><?= $no++; ?></td>
                                    <td><?= htmlspecialchars((string)($g['nip_tampil'] ?? '-')); ?></td>
                                    <td class="fw-semibold">
                                        <?= htmlspecialchars((string)($g['nama_tampil'] ?? '-')); ?>
                                        <?php if ($is_self): ?>
                                            <span class="badge bg-primary ms-1">Akun Anda</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars((string)($g['mapel_tampil'] ?? '-')); ?></td>
                                    <td><?= htmlspecialchars((string)($g['username_tampil'] ?? '-')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada data guru.</td>
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