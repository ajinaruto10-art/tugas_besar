<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
if ($role !== 'guru' && $role !== 'admin') {
    echo "<script>alert('Akses ditolak!'); window.location.href='login.php';</script>";
    exit;
}

$halaman_awal = ($role === 'admin') ? 'daftar_mengajar.php' : 'daftar_kelas_guru.php';

function tn_kolom(mysqli $koneksi, string $tabel): array {
    $kolom = [];
    $res = mysqli_query($koneksi, "SHOW COLUMNS FROM `$tabel`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $kolom[] = $row['Field'];
        }
    }
    return $kolom;
}

$kol_guru     = tn_kolom($koneksi, 'guru');
$kol_mapel    = tn_kolom($koneksi, 'mapel');
$kol_siswa    = tn_kolom($koneksi, 'siswa');
$kol_mengajar = tn_kolom($koneksi, 'mengajar');
$kol_raport   = tn_kolom($koneksi, 'nilail_raport');
$kol_users    = tn_kolom($koneksi, 'users');

$pk_guru  = in_array('id', $kol_guru, true) ? 'id' : (in_array('ID', $kol_guru, true) ? 'ID' : 'id');
$pk_mapel = in_array('ID', $kol_mapel, true) ? 'ID' : (in_array('id', $kol_mapel, true) ? 'id' : 'ID');
$pk_siswa = in_array('ID', $kol_siswa, true) ? 'ID' : (in_array('id', $kol_siswa, true) ? 'id' : 'id');
$pk_users = in_array('id', $kol_users, true) ? 'id' : (in_array('ID', $kol_users, true) ? 'ID' : 'id');

$fk_guru_mp  = in_array('guru_id', $kol_mapel, true) ? 'guru_id' : (in_array('id_guru', $kol_mapel, true) ? 'id_guru' : '');
$kol_nama_mp = in_array('nama_mapel', $kol_mapel, true) ? 'nama_mapel' : (in_array('mapel', $kol_mapel, true) ? 'mapel' : '');

// 1. Cari data guru yang sedang login
$user_id_login  = trim((string)($_SESSION['user_id'] ?? $_SESSION['id'] ?? ''));
$username_login = trim((string)($_SESSION['username'] ?? ''));
$id_guru_login  = '';

if (!empty($kol_guru)) {
    $kondisi_g = [];
    if ($user_id_login !== '') {
        $uid_esc = mysqli_real_escape_string($koneksi, $user_id_login);
        $kondisi_g[] = "g.`$pk_guru` = '$uid_esc'";
        if (in_array('user_id', $kol_guru, true)) {
            $kondisi_g[] = "g.`user_id` = '$uid_esc'";
        }
    }
    if ($username_login !== '' && !empty($kol_users) && in_array('user_id', $kol_guru, true) && in_array('username', $kol_users, true)) {
        $uname_esc = mysqli_real_escape_string($koneksi, $username_login);
        $kondisi_g[] = "u.`username` = '$uname_esc'";
    }

    if (!empty($kondisi_g)) {
        $join_u = (!empty($kol_users) && in_array('user_id', $kol_guru, true))
            ? "LEFT JOIN `users` u ON g.`user_id` = u.`$pk_users`"
            : "";
        $qg = mysqli_query($koneksi, "SELECT g.`$pk_guru` AS gid FROM `guru` g $join_u WHERE (" . implode(' OR ', $kondisi_g) . ") LIMIT 1");
        if ($qg && $rg = mysqli_fetch_assoc($qg)) {
            $id_guru_login = (string)$rg['gid'];
        }
    }
}

// 2. Ambil daftar mapel yang boleh diakses (untuk guru: hanya mapel miliknya)
$daftar_mapel    = [];
$mapel_ids_boleh = [];

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
    $sql_mp .= " ORDER BY `$kol_nama_mp` ASC";
    $qmp = mysqli_query($koneksi, $sql_mp);
    if ($qmp) {
        while ($rmp = mysqli_fetch_assoc($qmp)) {
            $mid = (string)($rmp[$pk_mapel] ?? '');
            if ($mid !== '') {
                $daftar_mapel[] = $rmp;
                $mapel_ids_boleh[] = $mid;
            }
        }
    }
}

// 3. Ambil daftar kelas yang diajar guru
$daftar_kelas = [];
$kelas_boleh  = [];

if ($role === 'admin') {
    if (!empty($kol_siswa) && in_array('kelas', $kol_siswa, true)) {
        $qk = mysqli_query($koneksi, "SELECT DISTINCT TRIM(`kelas`) AS kls FROM `siswa` WHERE `kelas` IS NOT NULL AND TRIM(`kelas`) <> '' ORDER BY kls ASC");
        if ($qk) {
            while ($rk = mysqli_fetch_assoc($qk)) {
                $daftar_kelas[] = (string)$rk['kls'];
                $kelas_boleh[]  = (string)$rk['kls'];
            }
        }
    }
} else {
    if (in_array('kelas', $kol_mapel, true)) {
        foreach ($daftar_mapel as $mp) {
            $k_mp = trim((string)($mp['kelas'] ?? ''));
            if ($k_mp !== '' && !in_array($k_mp, $kelas_boleh, true)) {
                $kelas_boleh[]  = $k_mp;
                $daftar_kelas[] = $k_mp;
            }
        }
    }

    if (!empty($mapel_ids_boleh) && !empty($kol_mengajar) && in_array('mapel_id', $kol_mengajar, true) && in_array('siswa_id', $kol_mengajar, true) && !empty($kol_siswa) && in_array('kelas', $kol_siswa, true)) {
        $in_mids = implode(',', array_map(fn($x) => "'" . mysqli_real_escape_string($koneksi, $x) . "'", $mapel_ids_boleh));
        $sql_k_mg = "
            SELECT DISTINCT TRIM(s.`kelas`) AS kls
            FROM `mengajar` mg
            INNER JOIN `siswa` s ON mg.`siswa_id` = s.`$pk_siswa`
            WHERE mg.`mapel_id` IN ($in_mids)
              AND s.`kelas` IS NOT NULL
              AND TRIM(s.`kelas`) <> ''
            ORDER BY kls ASC
        ";
        $qkm = mysqli_query($koneksi, $sql_k_mg);
        if ($qkm) {
            while ($rkm = mysqli_fetch_assoc($qkm)) {
                $k_mg = (string)$rkm['kls'];
                if ($k_mg !== '' && !in_array($k_mg, $kelas_boleh, true)) {
                    $kelas_boleh[]  = $k_mg;
                    $daftar_kelas[] = $k_mg;
                }
            }
        }
    }
}

$kelas_terpilih = trim((string)($_POST['kelas'] ?? $_GET['kelas'] ?? ($daftar_kelas[0] ?? '')));
$mapel_terpilih = trim((string)($_POST['mapel_id'] ?? $_GET['mapel_id'] ?? ($daftar_mapel[0][$pk_mapel] ?? '')));
$siswa_terpilih = trim((string)($_POST['siswa_id'] ?? $_GET['siswa_id'] ?? ''));

// 4. Proses simpan nilai
if (isset($_POST['submit'])) {
    $siswa_id_raw       = trim((string)($_POST['siswa_id'] ?? ''));
    $mapel_id_raw       = trim((string)($_POST['mapel_id'] ?? ''));
    $kelas_post_raw     = trim((string)($_POST['kelas'] ?? ''));
    $nilai_tugas        = (int)($_POST['nilai_tugas'] ?? 0);
    $nilai_pengetahuan = (int)($_POST['nilai_pengetahuan'] ?? 0);
    $nilai_keterampilan = (int)($_POST['nilai_keterampilan'] ?? 0);
    $keterangan_raw     = trim((string)($_POST['keterangan'] ?? ''));

    if ($siswa_id_raw === '' || $mapel_id_raw === '' || ($role === 'guru' && $kelas_post_raw === '')) {
        echo "<script>alert('Pilih kelas, siswa, dan mata pelajaran terlebih dahulu!'); window.history.back();</script>";
        exit;
    }

    if ($role === 'guru') {
        if (!in_array($mapel_id_raw, $mapel_ids_boleh, true)) {
            echo "<script>alert('Akses ditolak! Mata pelajaran ini bukan mapel yang Anda ampu.'); window.history.back();</script>";
            exit;
        }
        if (!in_array($kelas_post_raw, $kelas_boleh, true)) {
            echo "<script>alert('Akses ditolak! Anda tidak mengajar di kelas {$kelas_post_raw}.'); window.history.back();</script>";
            exit;
        }
    }

    if ($kelas_post_raw !== '' && !empty($kol_siswa) && in_array('kelas', $kol_siswa, true)) {
        $sid_chk = mysqli_real_escape_string($koneksi, $siswa_id_raw);
        $kls_chk = mysqli_real_escape_string($koneksi, $kelas_post_raw);
        $q_s_chk = mysqli_query($koneksi, "SELECT 1 FROM `siswa` WHERE `$pk_siswa` = '$sid_chk' AND TRIM(`kelas`) = '$kls_chk' LIMIT 1");
        if (!$q_s_chk || mysqli_num_rows($q_s_chk) === 0) {
            echo "<script>alert('Siswa tidak terdaftar pada kelas yang dipilih!'); window.history.back();</script>";
            exit;
        }
    }

    if (
        $nilai_tugas < 0 || $nilai_tugas > 100 ||
        $nilai_pengetahuan < 0 || $nilai_pengetahuan > 100 ||
        $nilai_keterampilan < 0 || $nilai_keterampilan > 100
    ) {
        echo "<script>alert('Nilai harus antara 0 sampai 100!'); window.history.back();</script>";
        exit;
    }

    if ($keterangan_raw === '') {
        $rata = round(($nilai_pengetahuan + $nilai_keterampilan) / 2);
        $keterangan_raw = ($rata >= 75) ? 'Tuntas' : 'Belum Tuntas';
    }

    $siswa_id_esc   = mysqli_real_escape_string($koneksi, $siswa_id_raw);
    $mapel_id_esc   = mysqli_real_escape_string($koneksi, $mapel_id_raw);
    $keterangan_esc = mysqli_real_escape_string($koneksi, $keterangan_raw);

    $fields = [];
    $values = [];

    if (in_array('siswa_id', $kol_raport, true)) {
        $fields[] = '`siswa_id`';
        $values[] = "'$siswa_id_esc'";
    }
    if (in_array('mapel_id', $kol_raport, true)) {
        $fields[] = '`mapel_id`';
        $values[] = "'$mapel_id_esc'";
    }
    if (in_array('nilai_tugas', $kol_raport, true)) {
        $fields[] = '`nilai_tugas`';
        $values[] = "'$nilai_tugas'";
    }
    if (in_array('nilial_pengetauan', $kol_raport, true)) {
        $fields[] = '`nilial_pengetauan`';
        $values[] = "'$nilai_pengetahuan'";
    } elseif (in_array('nilai_pengetahuan', $kol_raport, true)) {
        $fields[] = '`nilai_pengetahuan`';
        $values[] = "'$nilai_pengetahuan'";
    }
    if (in_array('nilail_keterampilan', $kol_raport, true)) {
        $fields[] = '`nilail_keterampilan`';
        $values[] = "'$nilai_keterampilan'";
    } elseif (in_array('nilai_keterampilan', $kol_raport, true)) {
        $fields[] = '`nilai_keterampilan`';
        $values[] = "'$nilai_keterampilan'";
    }
    if (in_array('keterangan', $kol_raport, true)) {
        $fields[] = '`keterangan`';
        $values[] = "'$keterangan_esc'";
    }

    if (empty($fields)) {
        echo "<script>alert('Struktur kolom tabel nilail_raport tidak dikenali.'); window.history.back();</script>";
        exit;
    }

    $sql_ins = "INSERT INTO `nilail_raport` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
    if (mysqli_query($koneksi, $sql_ins)) {
        echo "<script>alert('Nilai siswa berhasil disimpan!'); window.location.href='" . addslashes($halaman_awal) . "';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menyimpan nilai: " . addslashes(mysqli_error($koneksi)) . "'); window.history.back();</script>";
        exit;
    }
}

// 5. Ambil daftar siswa sesuai kelas terpilih
$siswa_rows = [];
if (!empty($kol_siswa)) {
    if ($role === 'guru' && empty($kelas_boleh)) {
        $siswa_rows = [];
    } else {
        $sql_s = "SELECT * FROM `siswa`";
        if ($kelas_terpilih !== '' && in_array('kelas', $kol_siswa, true)) {
            $kls_esc = mysqli_real_escape_string($koneksi, $kelas_terpilih);
            $sql_s .= " WHERE TRIM(`kelas`) = '$kls_esc'";
        }
        $sql_s .= " ORDER BY `nama` ASC";
        $qs = mysqli_query($koneksi, $sql_s);
        if ($qs) {
            while ($rs = mysqli_fetch_assoc($qs)) {
                $siswa_rows[] = $rs;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Raport - SIPN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php if ($role === 'guru'): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= htmlspecialchars($halaman_awal); ?>"><i class="bi bi-mortarboard-fill me-2"></i>SIPN - Panel Guru</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($halaman_awal); ?>"><i class="bi bi-house-door me-1"></i> Daftar Kelas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="tambah_nilai.php"><i class="bi bi-plus-circle me-1"></i> Input Nilai</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<div class="container my-4" style="max-width: 720px;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h4 class="mb-0 fw-bold">Input Nilai Raport Siswa</h4>
        </div>
        <div class="card-body p-4">
            <?php if ($role === 'guru' && empty($kelas_boleh)): ?>
                <div class="alert alert-warning">
                    Anda belum terdaftar mengajar kelas mana pun. Hubungi admin untuk menugaskan kelas/mapel terlebih dahulu.
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <!-- PILIH KELAS -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Kelas</label>
                    <select name="kelas" id="kelasSelect" class="form-select" required onchange="filterSiswaKelas()">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($daftar_kelas as $kls): ?>
                            <option value="<?= htmlspecialchars($kls); ?>" <?= ($kls === $kelas_terpilih) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($kls); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PILIH SISWA -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Siswa</label>
                    <select name="siswa_id" id="siswaSelect" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($siswa_rows as $siswa): ?>
                            <?php
                            $id_s   = (string)($siswa[$pk_siswa] ?? ($siswa['ID'] ?? ($siswa['id'] ?? '')));
                            $nama_s = (string)($siswa['nama'] ?? ($siswa['nama_siswa'] ?? 'Siswa'));
                            $nis_s  = (string)($siswa['nis'] ?? '-');
                            ?>
                            <option value="<?= htmlspecialchars($id_s); ?>" <?= ($id_s !== '' && $id_s === $siswa_terpilih) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($nama_s); ?> (NIS: <?= htmlspecialchars($nis_s); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PILIH MAPEL -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mata Pelajaran yang Anda Ampu</label>
                    <select name="mapel_id" class="form-select" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php foreach ($daftar_mapel as $mp): ?>
                            <?php
                            $id_m   = (string)($mp[$pk_mapel] ?? ($mp['ID'] ?? ($mp['id'] ?? '')));
                            $nama_m = (string)($mp[$kol_nama_mp] ?? 'Mata Pelajaran');
                            $kode_m = (string)($mp['kode_mapel'] ?? '-');
                            ?>
                            <option value="<?= htmlspecialchars($id_m); ?>" <?= ($id_m !== '' && $id_m === $mapel_terpilih) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($nama_m); ?> (Kode: <?= htmlspecialchars($kode_m); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- INPUT NILAI -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Nilai Tugas</label>
                        <input type="number" name="nilai_tugas" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Nilai Pengetahuan</label>
                        <input type="number" name="nilai_pengetahuan" class="form-control" min="0" max="100" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Nilai Keterampilan</label>
                        <input type="number" name="nilai_keterampilan" class="form-control" min="0" max="100" required>
                    </div>
                </div>

                <!-- KETERANGAN -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Tuntas">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= htmlspecialchars($halaman_awal); ?>" class="btn btn-secondary px-4">Kembali</a>
                    <button type="submit" name="submit" class="btn btn-primary px-4">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterSiswaKelas() {
    const kelasSelect = document.getElementById('kelasSelect');
    if (!kelasSelect) return;
    const selectedKelas = kelasSelect.value.trim();
    const url = new URL(window.location.href);
    if (selectedKelas) {
        url.searchParams.set('kelas', selectedKelas);
    } else {
        url.searchParams.delete('kelas');
    }
    window.location.href = url.toString();
}
</script>
</body>
</html>