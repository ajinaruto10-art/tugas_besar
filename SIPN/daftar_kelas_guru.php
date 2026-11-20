<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login & role
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
if ($role !== 'guru' && $role !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href='login.php';</script>";
    exit;
}

function dkg_kolom(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kol_guru     = dkg_kolom($koneksi, 'guru');
$kol_mapel    = dkg_kolom($koneksi, 'mapel');
$kol_siswa    = dkg_kolom($koneksi, 'siswa');
$kol_mengajar = dkg_kolom($koneksi, 'mengajar');
$kol_users    = dkg_kolom($koneksi, 'users');

$pk_guru  = in_array('id', $kol_guru, true) ? 'id' : (in_array('ID', $kol_guru, true) ? 'ID' : 'id');
$pk_mapel = in_array('ID', $kol_mapel, true) ? 'ID' : (in_array('id', $kol_mapel, true) ? 'id' : 'ID');
$pk_siswa = in_array('ID', $kol_siswa, true) ? 'ID' : (in_array('id', $kol_siswa, true) ? 'id' : 'id');
$pk_users = in_array('id', $kol_users, true) ? 'id' : (in_array('ID', $kol_users, true) ? 'ID' : 'id');

// Identifikasi guru yang sedang login
$user_id_login  = trim((string)($_SESSION['user_id'] ?? $_SESSION['id'] ?? ''));
$username_login = trim((string)($_SESSION['username'] ?? ''));
$guru_login     = null;

if (!empty($kol_guru)) {
    $kondisi = [];
    if ($user_id_login !== '') {
        $uid_esc = mysqli_real_escape_string($koneksi, $user_id_login);
        $kondisi[] = "g.`$pk_guru` = '$uid_esc'";
        if (in_array('user_id', $kol_guru, true)) {
            $kondisi[] = "g.`user_id` = '$uid_esc'";
        }
    }
    if ($username_login !== '' && !empty($kol_users) && in_array('user_id', $kol_guru, true) && in_array('username', $kol_users, true)) {
        $uname_esc = mysqli_real_escape_string($koneksi, $username_login);
        $kondisi[] = "u.`username` = '$uname_esc'";
    }

    if (!empty($kondisi)) {
        $join_u = (!empty($kol_users) && in_array('user_id', $kol_guru, true))
            ? "LEFT JOIN `users` u ON g.`user_id` = u.`$pk_users`"
            : "";
        $sql_g = "SELECT g.* FROM `guru` g $join_u WHERE (" . implode(' OR ', $kondisi) . ") LIMIT 1";
        $qg = mysqli_query($koneksi, $sql_g);
        if ($qg && mysqli_num_rows($qg) > 0) {
            $guru_login = mysqli_fetch_assoc($qg);
        }
    }
}

$id_guru_login   = (string)($guru_login[$pk_guru] ?? '');
$nama_guru_login = (string)($guru_login['nama'] ?? ($username_login !== '' ? $username_login : 'Guru'));

// Ambil mapel milik guru yang sedang login
$mapel_list     = [];
$mapel_ids_guru = [];
$fk_guru_mp     = in_array('id_guru', $kol_mapel, true) ? 'id_guru' : (in_array('guru_id', $kol_mapel, true) ? 'guru_id' : '');
$kol_nama_mp    = in_array('nama_mapel', $kol_mapel, true) ? 'nama_mapel' : (in_array('mapel', $kol_mapel, true) ? 'mapel' : '');

if (!empty($kol_mapel) && $kol_nama_mp !== '') {
    $sql_mp = "SELECT * FROM `mapel`";
    if ($role === 'guru') {
        if ($fk_guru_mp !== '' && $id_guru_login !== '') {
            $gid_esc = mysqli_real_escape_string($koneksi, $id_guru_login);
            $sql_mp .= " WHERE `$fk_guru_mp` = '$gid_esc'";
        } else {
            $sql_mp .= " WHERE 1=0";
        }
    }
    $sql_mp .= " ORDER BY `$pk_mapel` ASC";
    $qmp = mysqli_query($koneksi, $sql_mp);
    if ($qmp) {
        while ($rmp = mysqli_fetch_assoc($qmp)) {
            $mapel_list[] = $rmp;
            if (!empty($rmp[$pk_mapel])) {
                $mapel_ids_guru[] = (string)$rmp[$pk_mapel];
            }
        }
    }
}

// Kumpulkan kelas khusus milik guru ini saja
$kelas_mapel_pairs = [];
$seen_pairs        = [];

if ($role === 'admin') {
    if (!empty($kol_siswa) && in_array('kelas', $kol_siswa, true)) {
        $qk = mysqli_query(
            $koneksi,
            "SELECT DISTINCT TRIM(`kelas`) AS kls
             FROM `siswa`
             WHERE `kelas` IS NOT NULL AND TRIM(`kelas`) <> ''
             ORDER BY kls ASC"
        );
        $nama_mp_def = (string)($mapel_list[0][$kol_nama_mp] ?? ($guru_login['mapel'] ?? 'Semua Mata Pelajaran'));
        $id_mp_def   = (string)($mapel_list[0][$pk_mapel] ?? '');
        if ($qk) {
            while ($rk = mysqli_fetch_assoc($qk)) {
                $kls = (string)$rk['kls'];
                $key = $kls . '|' . $id_mp_def;
                if (!isset($seen_pairs[$key])) {
                    $seen_pairs[$key] = true;
                    $kelas_mapel_pairs[] = [
                        'kelas'      => $kls,
                        'mapel_id'   => $id_mp_def,
                        'nama_mapel' => $nama_mp_def,
                    ];
                }
            }
        }
    }
} else {
    // 1. Dari kolom kelas di tabel mapel milik guru
    if (in_array('kelas', $kol_mapel, true)) {
        foreach ($mapel_list as $mp) {
            $kls = trim((string)($mp['kelas'] ?? ''));
            $mid = (string)($mp[$pk_mapel] ?? '');
            $mmp = (string)($mp[$kol_nama_mp] ?? ($guru_login['mapel'] ?? '-'));
            if ($kls !== '') {
                $key = $kls . '|' . $mid;
                if (!isset($seen_pairs[$key])) {
                    $seen_pairs[$key] = true;
                    $kelas_mapel_pairs[] = [
                        'kelas'      => $kls,
                        'mapel_id'   => $mid,
                        'nama_mapel' => $mmp,
                    ];
                }
            }
        }
    }

    // 2. Dari tabel mengajar -> siswa.kelas untuk mapel milik guru
    if (
        !empty($mapel_ids_guru) &&
        !empty($kol_mengajar) &&
        in_array('mapel_id', $kol_mengajar, true) &&
        in_array('siswa_id', $kol_mengajar, true) &&
        !empty($kol_siswa) &&
        in_array('kelas', $kol_siswa, true)
    ) {
        $in_mids = implode(',', array_map(fn($x) => "'" . mysqli_real_escape_string($koneksi, $x) . "'", $mapel_ids_guru));
        $sql_mg = "
            SELECT DISTINCT
                TRIM(s.`kelas`) AS kls,
                mg.`mapel_id` AS mid
            FROM `mengajar` mg
            INNER JOIN `siswa` s ON mg.`siswa_id` = s.`$pk_siswa`
            WHERE mg.`mapel_id` IN ($in_mids)
              AND s.`kelas` IS NOT NULL
              AND TRIM(s.`kelas`) <> ''
            ORDER BY kls ASC
        ";
        $qmg = mysqli_query($koneksi, $sql_mg);
        if ($qmg) {
            $nama_by_mid = [];
            foreach ($mapel_list as $mp) {
                $nama_by_mid[(string)$mp[$pk_mapel]] = (string)($mp[$kol_nama_mp] ?? '-');
            }
            while ($rmg = mysqli_fetch_assoc($qmg)) {
                $kls = (string)$rmg['kls'];
                $mid = (string)$rmg['mid'];
                $mmp = $nama_by_mid[$mid] ?? ($guru_login['mapel'] ?? '-');
                $key = $kls . '|' . $mid;
                if (!isset($seen_pairs[$key])) {
                    $seen_pairs[$key] = true;
                    $kelas_mapel_pairs[] = [
                        'kelas'      => $kls,
                        'mapel_id'   => $mid,
                        'nama_mapel' => $mmp,
                    ];
                }
            }
        }
    }
}

// Hitung jumlah siswa per kelas yang muncul
$rows_kelas_diajar = [];
foreach ($kelas_mapel_pairs as $pair) {
    $jml_siswa = 0;
    if (!empty($kol_siswa) && in_array('kelas', $kol_siswa, true)) {
        $kls_esc = mysqli_real_escape_string($koneksi, $pair['kelas']);
        $qc = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM `siswa` WHERE TRIM(`kelas`) = '$kls_esc'");
        if ($qc && $rc = mysqli_fetch_assoc($qc)) {
            $jml_siswa = (int)($rc['jml'] ?? 0);
        }
    }
    $rows_kelas_diajar[] = [
        'kelas'        => $pair['kelas'],
        'mapel_id'     => $pair['mapel_id'],
        'nama_mapel'   => $pair['nama_mapel'],
        'jumlah_siswa' => $jml_siswa,
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kelas yang Diajar - SIPN</title>
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

<?php if (file_exists('navbar.php')) { include 'navbar.php'; } ?>

<div class="container my-4">
    <h1 class="text-center fw-normal mb-2" style="font-size: 2.2rem;">Daftar Kelas yang Diajar</h1>
    <p class="text-center text-muted mb-4">
        Guru: <strong><?= htmlspecialchars($nama_guru_login); ?></strong> — Klik kelas untuk masuk ke daftar nilai siswa
    </p>

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
        <table class="table table-bordered table-striped" id="tabelKelasDiajar">
            <thead>
                <tr>
                    <th style="width: 70px;">No</th>
                    <th style="width: 160px;">Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th style="width: 150px;">Jumlah Siswa</th>
                    <th style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbodyKelas">
                <?php if (!empty($rows_kelas_diajar)): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($rows_kelas_diajar as $item): ?>
                        <?php
                        $link_kelas = "nilai_siswa_guru.php?kelas=" . urlencode($item['kelas'])
                            . ($item['mapel_id'] !== '' ? "&mapel_id=" . urlencode($item['mapel_id']) : "");
                        ?>
                        <tr class="data-row">
                            <td class="fw-bold"><?= $no++; ?></td>
                            <td>
                                <a href="<?= htmlspecialchars($link_kelas); ?>" class="fw-semibold text-decoration-none">
                                    <?= htmlspecialchars($item['kelas']); ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($item['nama_mapel']); ?></td>
                            <td><?= (int)$item['jumlah_siswa']; ?> siswa</td>
                            <td>
                                <a href="<?= htmlspecialchars($link_kelas); ?>" class="btn btn-primary btn-sm fw-semibold px-3">
                                    Masuk Kelas
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Belum ada kelas yang tercatat untuk guru ini.
                        </td>
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
    const rows = Array.from(document.querySelectorAll('#tbodyKelas .data-row'));

    function updateTable() {
        const keyword = (searchInput.value || '').toLowerCase().trim();
        const limit = perPageSelect.value === 'all' ? Infinity : parseInt(perPageSelect.value, 10);

        const filtered = rows.filter(r => !keyword || r.textContent.toLowerCase().includes(keyword));
        rows.forEach(r => r.style.display = 'none');
        filtered.forEach((r, idx) => {
            if (idx < limit) r.style.display = '';
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