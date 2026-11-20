<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$role           = strtolower(trim((string)($_SESSION['role'] ?? '')));
$username_login = (string)($_SESSION['username'] ?? '');
$username_aman  = mysqli_real_escape_string($koneksi, $username_login);

function ambil_kolom_tabel2(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kolom_users = ambil_kolom_tabel2($koneksi, 'users');
$kolom_siswa = ambil_kolom_tabel2($koneksi, 'siswa');
$kolom_mapel = ambil_kolom_tabel2($koneksi, 'mapel');
$kolom_nilai = ambil_kolom_tabel2($koneksi, 'nilai');

$id_siswa_login = '';
$user_id_login  = (string)($_SESSION['user_id'] ?? ($_SESSION['id'] ?? ''));
$nama_siswa     = $username_login !== '' ? $username_login : 'Siswa';
$kelas_siswa    = 'Belum Diatur';
$nis_siswa      = '-';

if ($user_id_login === '' && $username_login !== '' && !empty($kolom_users)) {
    $q_user_s = mysqli_query($koneksi, "SELECT * FROM users WHERE LOWER(TRIM(username)) = LOWER(TRIM('$username_aman')) LIMIT 1");
    if ($q_user_s && ($d_user_s = mysqli_fetch_assoc($q_user_s))) {
        $user_id_login = (string)($d_user_s['ID'] ?? ($d_user_s['id'] ?? ''));
    }
}

$pk_siswa = in_array('ID', $kolom_siswa, true) ? 'ID' : (in_array('id', $kolom_siswa, true) ? 'id' : 'ID');
$pk_mapel = in_array('ID', $kolom_mapel, true) ? 'ID' : (in_array('id', $kolom_mapel, true) ? 'id' : 'ID');
$kol_kelas_siswa = in_array('kelas', $kolom_siswa, true) ? 'kelas' : (in_array('nama_kelas', $kolom_siswa, true) ? 'nama_kelas' : '');

if ($role === 'siswa' && !empty($kolom_siswa)) {
    $syarat_s = [];
    if ($user_id_login !== '') {
        $uid_esc = mysqli_real_escape_string($koneksi, $user_id_login);
        if (in_array('user_id', $kolom_siswa, true)) {
            $syarat_s[] = "user_id = '$uid_esc'";
        }
        if (in_array('id_user', $kolom_siswa, true)) {
            $syarat_s[] = "id_user = '$uid_esc'";
        }
    }
    if ($username_login !== '') {
        $uname_esc = mysqli_real_escape_string($koneksi, strtolower(trim($username_login)));
        foreach (['username', 'nis', 'nisn', 'nama'] as $ks) {
            if (in_array($ks, $kolom_siswa, true)) {
                $syarat_s[] = "LOWER(TRIM(`$ks`)) = '$uname_esc'";
            }
        }
    }

    $d_siswa = null;
    if (!empty($syarat_s)) {
        $q_siswa = mysqli_query($koneksi, "SELECT * FROM siswa WHERE (" . implode(' OR ', $syarat_s) . ") LIMIT 1");
        if ($q_siswa) {
            $d_siswa = mysqli_fetch_assoc($q_siswa);
        }
    }

    if ($d_siswa) {
        $id_siswa_login = (string)($d_siswa[$pk_siswa] ?? ($d_siswa['id'] ?? ($d_siswa['ID'] ?? '')));
        $nama_siswa     = (string)($d_siswa['nama'] ?? ($d_siswa['nama_siswa'] ?? ($username_login ?: 'Siswa')));
        $kelas_siswa    = (string)($d_siswa[$kol_kelas_siswa] ?? ($d_siswa['kelas'] ?? ($d_siswa['nama_kelas'] ?? 'Belum Diatur')));
        $nis_siswa      = (string)($d_siswa['nis'] ?? ($d_siswa['nisn'] ?? '-'));
    }
}

// Ambil daftar siswa yang sekelas dengan siswa yang sedang login
$rows_teman_sekelas = [];
if ($role === 'siswa' && !empty($kolom_siswa) && $kelas_siswa !== '' && $kelas_siswa !== 'Belum Diatur' && $kol_kelas_siswa !== '') {
    $kelas_norm = strtolower(preg_replace('/\s+/', ' ', trim($kelas_siswa)));
    $kelas_esc  = mysqli_real_escape_string($koneksi, $kelas_norm);
    $order_s    = in_array('nama', $kolom_siswa, true) ? 'nama ASC' : (in_array('nama_siswa', $kolom_siswa, true) ? 'nama_siswa ASC' : "`$pk_siswa` ASC");

    $sql_sekelas_fallback = "
        SELECT *
        FROM siswa
        WHERE LOWER(TRIM(`$kol_kelas_siswa`)) = '$kelas_esc'
           OR REPLACE(LOWER(TRIM(`$kol_kelas_siswa`)), '-', ' ') = REPLACE('$kelas_esc', '-', ' ')
        ORDER BY $order_s
    ";
    $q_sekelas = mysqli_query($koneksi, $sql_sekelas_fallback);
    if ($q_sekelas) {
        while ($rs = mysqli_fetch_assoc($q_sekelas)) {
            $rows_teman_sekelas[] = $rs;
        }
    }
}

$rows_nilai = [];
$error_tampil = '';

if (empty($kolom_nilai)) {
    $error_tampil = "Tabel 'nilai' belum ditemukan di database.";
} else {
    $join_sql = "";
    $sumber_kelas = [];

    if (in_array('kelas', $kolom_nilai, true)) {
        $sumber_kelas[] = "NULLIF(TRIM(n.kelas), '')";
    }
    if (in_array('nama_kelas', $kolom_nilai, true)) {
        $sumber_kelas[] = "NULLIF(TRIM(n.nama_kelas), '')";
    }

    $select_nama_siswa = in_array('nama_siswa', $kolom_nilai, true) ? "COALESCE(NULLIF(TRIM(n.nama_siswa), ''), '-')" : "'-'";
    $select_nama_mapel = in_array('nama_mapel', $kolom_nilai, true) ? "COALESCE(NULLIF(TRIM(n.nama_mapel), ''), '-')" : (in_array('mapel', $kolom_nilai, true) ? "COALESCE(NULLIF(TRIM(n.mapel), ''), '-')" : "'-'");

    if (!empty($kolom_siswa)) {
        $kondisi_join_s = [];
        if (in_array('nis', $kolom_nilai, true) && in_array('nis', $kolom_siswa, true)) {
            $kondisi_join_s[] = "(TRIM(n.nis) != '' AND TRIM(n.nis) = TRIM(s.nis))";
        }
        if (in_array('siswa_id', $kolom_nilai, true)) {
            $kondisi_join_s[] = "n.siswa_id = s.`$pk_siswa`";
        }
        if (in_array('nama_siswa', $kolom_nilai, true) && in_array('nama', $kolom_nilai, true)) {
            $kondisi_join_s[] = "(TRIM(n.nama_siswa) != '' AND LOWER(TRIM(n.nama_siswa)) = LOWER(TRIM(s.nama)))";
        }

        if (!empty($kondisi_join_s)) {
            $join_sql .= " LEFT JOIN siswa s ON (" . implode(' OR ', $kondisi_join_s) . ") ";
            $kol_s_nama = in_array('nama', $kolom_siswa, true) ? 's.nama' : (in_array('nama_siswa', $kolom_siswa, true) ? 's.nama_siswa' : '');
            if ($kol_s_nama !== '') {
                $select_nama_siswa = "COALESCE(NULLIF(TRIM($kol_s_nama), ''), $select_nama_siswa)";
            }
            if ($kol_kelas_siswa !== '') {
                $sumber_kelas[] = "NULLIF(TRIM(s.`$kol_kelas_siswa`), '')";
            }
        }
    }

    if (!empty($kolom_mapel)) {
        $kondisi_join_m = [];
        if (in_array('id_mapel', $kolom_nilai, true)) {
            $kondisi_join_m[] = "n.id_mapel = m.`$pk_mapel`";
        }
        if (in_array('mapel_id', $kolom_nilai, true)) {
            $kondisi_join_m[] = "n.mapel_id = m.`$pk_mapel`";
        }
        if (!empty($kondisi_join_m)) {
            $join_sql .= " LEFT JOIN mapel m ON (" . implode(' OR ', $kondisi_join_m) . ") ";
            $kol_m_nama = in_array('nama_mapel', $kolom_nilai, true) ? 'm.nama_mapel' : (in_array('mapel', $kolom_nilai, true) ? 'm.mapel' : '');
            if ($kol_m_nama !== '') {
                $select_nama_mapel = "COALESCE(NULLIF(TRIM($kol_m_nama), ''), $select_nama_mapel)";
            }
        }
    }

    if ($role === 'siswa' && $kelas_siswa !== '' && $kelas_siswa !== 'Belum Diatur') {
        $k_s_esc = mysqli_real_escape_string($koneksi, trim($kelas_siswa));
        $sumber_kelas[] = "'$k_s_esc'";
    }
    $sumber_kelas[] = "'-'";
    $select_kelas = "COALESCE(" . implode(', ', $sumber_kelas) . ")";

    $where_nilai = "";
    if ($role === 'siswa') {
        $syarat_w = [];
        if ($nis_siswa !== '' && $nis_siswa !== '-' && in_array('nis', $kolom_nilai, true)) {
            $nis_esc = mysqli_real_escape_string($koneksi, trim($nis_siswa));
            $syarat_w[] = "TRIM(n.nis) = '$nis_esc'";
        }
        if ($nama_siswa !== '' && in_array('nama_siswa', $kolom_nilai, true)) {
            $ns_esc = mysqli_real_escape_string($koneksi, strtolower(trim($nama_siswa)));
            $syarat_w[] = "LOWER(TRIM(n.nama_siswa)) = '$ns_esc'";
        }
        if (!empty($syarat_w)) {
            $where_nilai = " WHERE (" . implode(' OR ', $syarat_w) . ") ";
        } else {
            $where_nilai = " WHERE 1 = 0 ";
        }
    }

    $pk_nilai = in_array('id', $kolom_nilai, true) ? 'n.id' : (in_array('ID', $kolom_nilai, true) ? 'n.ID' : '1');
    $sql_tampil = "
        SELECT
            n.*,
            $select_kelas AS kelas_tampil,
            $select_nama_siswa AS nama_siswa_tampil,
            $select_nama_mapel AS nama_mapel_tampil
        FROM nilai n
        $join_sql
        $where_nilai
        ORDER BY $pk_nilai DESC
    ";

    $res_tampil = mysqli_query($koneksi, $sql_tampil);
    if ($res_tampil) {
        while ($r = mysqli_fetch_assoc($res_tampil)) {
            $rows_nilai[] = $r;
        }
    } else {
        $error_tampil = mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php if (file_exists('navbar.php')) { include 'navbar.php'; } ?>

<div class="container my-4">

    <?php if ($role === 'siswa'): ?>
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Informasi Siswa</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <small class="text-muted d-block">Nama Lengkap</small>
                        <span class="fs-5 fw-bold text-dark"><?= htmlspecialchars($nama_siswa); ?></span>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <small class="text-muted d-block">Kelas</small>
                        <span class="badge bg-success fs-6"><i class="bi bi-building me-1"></i><?= htmlspecialchars($kelas_siswa); ?></span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">NIS / Username</small>
                        <span class="fw-bold text-secondary"><?= htmlspecialchars($nis_siswa !== '-' ? $nis_siswa : $username_login); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL SISWA SATU KELAS -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-people-fill me-2 text-success"></i>Daftar Siswa Kelas <?= htmlspecialchars($kelas_siswa); ?>
                </h5>
                <span class="badge bg-secondary"><?= count($rows_teman_sekelas); ?> Siswa</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle border mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 70px;">No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rows_teman_sekelas)): ?>
                                <?php $no_s = 1; ?>
                                <?php foreach ($rows_teman_sekelas as $ts): ?>
                                    <?php
                                        $ts_nis   = (string)($ts['nis'] ?? ($ts['nisn'] ?? '-'));
                                        $ts_nama  = (string)($ts['nama'] ?? ($ts['nama_siswa'] ?? '-'));
                                        $ts_kelas = (string)($ts[$kol_kelas_siswa] ?? ($ts['kelas'] ?? ($ts['nama_kelas'] ?? $kelas_siswa)));
                                        $is_diri  = ($id_siswa_login !== '' && (string)($ts[$pk_siswa] ?? '') === $id_siswa_login)
                                            || (strtolower(trim($ts_nama)) === strtolower(trim($nama_siswa)));
                                    ?>
                                    <tr class="<?= $is_diri ? 'table-success fw-semibold' : ''; ?>">
                                        <td><?= $no_s++; ?></td>
                                        <td><?= htmlspecialchars($ts_nis); ?></td>
                                        <td>
                                            <?= htmlspecialchars($ts_nama); ?>
                                            <?php if ($is_diri): ?>
                                                <span class="badge bg-success ms-1">Anda</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($ts_kelas); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        Belum ada data siswa lain yang terdaftar pada kelas <?= htmlspecialchars($kelas_siswa); ?>.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error_tampil !== ''): ?>
        <div class="alert alert-danger">
            <strong>Error:</strong> <?= htmlspecialchars($error_tampil); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark">
                <?= ($role === 'guru' || $role === 'admin') ? 'Daftar Siswa yang Diajar & Nilai' : 'Daftar Nilai Saya'; ?>
            </h4>
            <?php if ($role === 'guru' || $role === 'admin'): ?>
                <a href="input_nilai.php" class="btn btn-dark btn-sm fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i> Input Nilai Baru
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle border">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <?php if ($role === 'guru' || $role === 'admin'): ?>
                                <th>Nama Siswa</th>
                            <?php endif; ?>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Perilaku</th>
                            <th>Nilai</th>
                            <th>Pengetahuan</th>
                            <th>Keterampilan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (!empty($rows_nilai)):
                            foreach ($rows_nilai as $row):
                                $kls_row = '-';
                                foreach (['kelas_tampil', 'kelas', 'nama_kelas'] as $kk) {
                                    if (isset($row[$kk]) && trim((string)$row[$kk]) !== '') {
                                        $kls_row = trim((string)$row[$kk]);
                                        break;
                                    }
                                }
                                if ($kls_row === '-' && $role === 'siswa' && $kelas_siswa !== '' && $kelas_siswa !== 'Belum Diatur') {
                                    $kls_row = $kelas_siswa;
                                }

                                $perilaku_row = '-';
                                foreach (['perilaku', 'sikap', 'keterangan'] as $kp) {
                                    if (isset($row[$kp]) && trim((string)$row[$kp]) !== '') {
                                        $perilaku_row = trim((string)$row[$kp]);
                                        break;
                                    }
                                }

                                $n_pengh = $row['nilai_pengetahuan'] ?? '0';
                                $n_ketr  = $row['nilai_keterampilan'] ?? '0';
                                $n_utama = is_numeric($n_pengh) && is_numeric($n_ketr)
                                    ? round(((float)$n_pengh + (float)$n_ketr) / 2, 2)
                                    : ($row['nilai'] ?? $n_pengh);
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <?php if ($role === 'guru' || $role === 'admin'): ?>
                                    <td><?= htmlspecialchars((string)($row['nama_siswa_tampil'] ?? '-')); ?></td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($kls_row); ?></td>
                                <td><?= htmlspecialchars((string)($row['nama_mapel_tampil'] ?? '-')); ?></td>
                                <td><?= htmlspecialchars($perilaku_row); ?></td>
                                <td class="fw-bold"><?= htmlspecialchars((string)$n_utama); ?></td>
                                <td><?= htmlspecialchars((string)$n_pengh); ?></td>
                                <td><?= htmlspecialchars((string)$n_ketr); ?></td>
                            </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <tr>
                                <td colspan="<?= ($role === 'guru' || $role === 'admin') ? '8' : '7'; ?>" class="text-center text-muted py-4">
                                    Belum ada data nilai yang tersimpan.
                                </td>
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