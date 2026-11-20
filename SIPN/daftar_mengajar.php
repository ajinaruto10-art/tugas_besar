<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek hak akses admin
if (!isset($_SESSION['login']) || strtolower(trim((string)($_SESSION['role'] ?? ''))) !== 'admin') {
    header("Location: login.php");
    exit;
}

function dm_kolom(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kol_mapel    = dm_kolom($koneksi, 'mapel');
$kol_guru     = dm_kolom($koneksi, 'guru');
$kol_mengajar = dm_kolom($koneksi, 'mengajar');
$kol_siswa    = dm_kolom($koneksi, 'siswa');

$pk_mapel    = in_array('ID', $kol_mapel, true) ? 'ID' : (in_array('id', $kol_mapel, true) ? 'id' : 'ID');
$fk_guru_mp  = in_array('id_guru', $kol_mapel, true) ? 'id_guru' : (in_array('guru_id', $kol_mapel, true) ? 'guru_id' : '');
$pk_guru     = in_array('id', $kol_guru, true) ? 'id' : (in_array('ID', $kol_guru, true) ? 'ID' : 'id');
$pk_siswa    = in_array('ID', $kol_siswa, true) ? 'ID' : (in_array('id', $kol_siswa, true) ? 'id' : 'id');
$pk_mengajar = in_array('id', $kol_mengajar, true) ? 'id' : (in_array('ID', $kol_mengajar, true) ? 'ID' : 'id');

$join_guru = "";
if ($fk_guru_mp !== '') {
    $kondisi_guru = "m.`$fk_guru_mp` = g.`$pk_guru`";
    if (in_array('user_id', $kol_guru, true)) {
        $kondisi_guru = "(m.`$fk_guru_mp` = g.`$pk_guru` OR m.`$fk_guru_mp` = g.user_id)";
    }
    $join_guru = "LEFT JOIN guru g ON $kondisi_guru";
}

$kol_kelas_mp = in_array('kelas', $kol_mapel, true) ? "NULLIF(TRIM(m.kelas), '')" : "NULL";
$kol_kelas_s  = in_array('kelas', $kol_siswa, true) ? "NULLIF(TRIM(s.kelas), '')" : "NULL";

$join_mengajar = "";
$join_siswa    = "";

if (!empty($kol_mengajar) && in_array('mapel_id', $kol_mengajar, true)) {
    $join_mengajar = "LEFT JOIN mengajar mg ON mg.mapel_id = m.`$pk_mapel`";
    if (!empty($kol_siswa) && in_array('siswa_id', $kol_mengajar, true)) {
        $join_siswa = "LEFT JOIN siswa s ON mg.siswa_id = s.`$pk_siswa`";
    }
}

$select_kode_mp = in_array('kode_mapel', $kol_mapel, true) ? "m.kode_mapel" : (in_array('kode', $kol_mapel, true) ? "m.kode" : "''");
$select_nama_mp = in_array('nama_mapel', $kol_mapel, true) ? "m.nama_mapel" : (in_array('mapel', $kol_mapel, true) ? "m.mapel" : "''");
$select_nama_g  = in_array('nama', $kol_guru, true) ? "g.nama" : (in_array('nama_guru', $kol_guru, true) ? "g.nama_guru" : "''");
$select_mg_id   = !empty($kol_mengajar) ? "MAX(mg.`$pk_mengajar`)" : "NULL";

$group_by_cols = ["m.`$pk_mapel`"];
if (in_array('kode_mapel', $kol_mapel, true)) $group_by_cols[] = "m.kode_mapel";
if (in_array('kode', $kol_mapel, true)) $group_by_cols[] = "m.kode";
if (in_array('nama_mapel', $kol_mapel, true)) $group_by_cols[] = "m.nama_mapel";
if (in_array('mapel', $kol_mapel, true)) $group_by_cols[] = "m.mapel";
if (in_array('kelas', $kol_mapel, true)) $group_by_cols[] = "m.kelas";
if ($fk_guru_mp !== '') $group_by_cols[] = "m.`$fk_guru_mp`";
if (in_array('nama', $kol_guru, true)) $group_by_cols[] = "g.nama";
if (in_array('nama_guru', $kol_guru, true)) $group_by_cols[] = "g.nama_guru";
if (!empty($kol_siswa) && in_array('kelas', $kol_siswa, true)) $group_by_cols[] = "s.kelas";

$query = "
    SELECT
        m.`$pk_mapel` AS mapel_id,
        $select_kode_mp AS kode_mapel,
        $select_nama_mp AS nama_mapel,
        $select_nama_g AS nama_guru,
        COALESCE($kol_kelas_mp, $kol_kelas_s) AS kelas_tampil,
        $select_mg_id AS mengajar_id
    FROM mapel m
    $join_guru
    $join_mengajar
    $join_siswa
    GROUP BY " . implode(', ', array_unique($group_by_cols)) . "
    ORDER BY
        CASE WHEN mengajar_id IS NULL THEN 1 ELSE 0 END ASC,
        mengajar_id DESC,
        m.`$pk_mapel` DESC
";

$result = mysqli_query($koneksi, $query);

$rows = [];
if ($result) {
    while ($r = mysqli_fetch_assoc($result)) {
        $rows[] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Guru Mengajar - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
        }
        .table thead th {
            background-color: #f2f2f2;
            border-bottom: 1px solid #dee2e6;
            font-weight: 700;
            color: #111;
            vertical-align: middle;
        }
        .table tbody td {
            vertical-align: middle;
            padding: 12px 10px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-4">
    <h1 class="text-center fw-normal mb-4" style="font-size: 2.2rem;">Daftar Guru Mengajar</h1>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <span>Tampilkan</span>
            <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="all">Semua</option>
            </select>
            <span>data per halaman</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <label for="searchInput" class="mb-0">Cari:</label>
            <input type="text" id="searchInput" class="form-control form-control-sm" style="width: 200px;" placeholder="">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="tabelMengajar">
            <thead>
                <tr>
                    <th style="width: 70px;">No</th>
                    <th>Nama Guru</th>
                    <th style="width: 120px;">Kelas</th>
                    <th>Nama Mata Pelajaran</th>
                    <th style="width: 160px;">Kode Mata Pelajaran</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbodyMengajar">
                <?php if (count($rows) > 0): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $nama_guru  = !empty($row['nama_guru']) ? $row['nama_guru'] : '-';
                        $kelas      = !empty($row['kelas_tampil']) ? $row['kelas_tampil'] : '-';
                        $nama_mapel = !empty($row['nama_mapel']) ? $row['nama_mapel'] : '-';
                        $kode_mapel = !empty($row['kode_mapel']) ? $row['kode_mapel'] : '-';
                        $id_edit    = $row['mapel_id'] ?? '';
                        $id_hapus   = $row['mengajar_id'] ?? ($row['mapel_id'] ?? '');
                        ?>
                        <tr class="data-row">
                            <td class="fw-bold"><?= $no++; ?></td>
                            <td><?= htmlspecialchars($nama_guru); ?></td>
                            <td><?= htmlspecialchars($kelas); ?></td>
                            <td><?= htmlspecialchars($nama_mapel); ?></td>
                            <td><?= htmlspecialchars($kode_mapel); ?></td>
                            <td>
                                <a href="edit_mengajar.php?mapel_id=<?= urlencode((string)$id_edit); ?>&id=<?= urlencode((string)$id_hapus); ?>" class="btn btn-warning btn-sm fw-semibold text-dark">Edit</a>
                                <a href="hapus_mengajar.php?mapel_id=<?= urlencode((string)$id_edit); ?>&id=<?= urlencode((string)$id_hapus); ?>" class="btn btn-danger btn-sm fw-semibold">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr id="emptyRow">
                        <td colspan="6" class="text-center text-muted py-4">Belum ada data guru mengajar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    const searchInput = document.getElementById('searchInput');
    const perPageSelect = document.getElementById('perPageSelect');
    const rows = Array.from(document.querySelectorAll('#tbodyMengajar .data-row'));

    function updateTable() {
        const keyword = (searchInput.value || '').toLowerCase().trim();
        const perPageVal = perPageSelect.value;
        const limit = perPageVal === 'all' ? Infinity : parseInt(perPageVal, 10);

        const filtered = rows.filter(row => {
            if (!keyword) return true;
            return row.textContent.toLowerCase().includes(keyword);
        });

        rows.forEach(r => r.style.display = 'none');

        filtered.forEach((row, idx) => {
            if (idx < limit) {
                row.style.display = '';
            }
        });
    }

    if (searchInput && perPageSelect) {
        searchInput.addEventListener('input', updateTable);
        perPageSelect.addEventListener('change', updateTable);
        updateTable();
    }
})();
</script>
</body>
</html>