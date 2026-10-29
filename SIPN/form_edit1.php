<?php

$koneksi = mysqli_connect("localhost", "root", "", "sekolah_sangkuriang1");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// ==========================================
// AMBIL ID DARI URL
// ==========================================
$id = $_GET['id'] ?? 0;

// ==========================================
// AMBIL DATA USERS
// ==========================================
$queryUser = mysqli_query(
    $koneksi,
    "SELECT * FROM users WHERE id = '$id'"
);

if (!$queryUser || mysqli_num_rows($queryUser) == 0) {
    die("Data user tidak ditemukan.");
}

$user = mysqli_fetch_assoc($queryUser);

// ==========================================
// AMBIL ROLE
// ==========================================
$role = $user['role'];

// ==========================================
// AMBIL DATA SISWA
// ==========================================
$siswa = null;

if ($role == 'siswa') {

    $querySiswa = mysqli_query(
        $koneksi,
        "SELECT * FROM siswa WHERE user_id = '$id'"
    );

    if ($querySiswa) {
        $siswa = mysqli_fetch_assoc($querySiswa);
    }
}

// ==========================================
// AMBIL DATA GURU
// ==========================================
$guru = null;

if ($role == 'guru') {

    $queryGuru = mysqli_query(
        $koneksi,
        "SELECT * FROM guru WHERE user_id = '$id'"
    );

    if ($queryGuru) {
        $guru = mysqli_fetch_assoc($queryGuru);
    }
}

// ==========================================
// KETIKA TOMBOL SIMPAN DIKLIK
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // ==========================================
    // UPDATE DATA SISWA
    // ==========================================
    if ($role == 'siswa') {

        $nis = $_POST['nis'];
        $nama_siswa = $_POST['nama_siswa'];
        $kelas = $_POST['kelas'];
        $jenis_kelamin = $_POST['jenis_kelamin'];

        $updateSiswa = mysqli_query(
            $koneksi,
            "
            UPDATE siswa SET
                nis = '$nis',
                nama_siswa = '$nama_siswa',
                kelas = '$kelas',
                jenis_kelamin = '$jenis_kelamin'
            WHERE user_id = '$id'
            "
        );

        if (!$updateSiswa) {
            die(
                "Gagal mengubah data siswa: " .
                mysqli_error($koneksi)
            );
        }
    }


    // ==========================================
    // UPDATE DATA GURU
    // ==========================================
    elseif ($role == 'guru') {

        $nip = $_POST['nip'];
        $nama = $_POST['nama'];
        $jenis_kelamin = $_POST['jenis_kelamin'];

        $updateGuru = mysqli_query(
            $koneksi,
            "
            UPDATE guru SET
                nip = '$nip',
                nama = '$nama',
                jenis_kelamin = '$jenis_kelamin'
            WHERE user_id = '$id'
            "
        );

        if (!$updateGuru) {
            die(
                "Gagal mengubah data guru: " .
                mysqli_error($koneksi)
            );
        }
    }


    // ==========================================
    // UPDATE DATA ADMIN
    // ==========================================
    elseif ($role == 'admin') {

        $username = $_POST['username'];
        $password = $_POST['password'] ?? '';

        // Jika password diisi
        if (!empty($password)) {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $updateAdmin = mysqli_query(
                $koneksi,
                "
                UPDATE users SET
                    username = '$username',
                    password = '$passwordHash'
                WHERE id = '$id'
                "
            );

        } else {

            // Jika password kosong,
            // password lama tetap digunakan

            $updateAdmin = mysqli_query(
                $koneksi,
                "
                UPDATE users SET
                    username = '$username'
                WHERE id = '$id'
                "
            );
        }

        if (!$updateAdmin) {
            die(
                "Gagal mengubah data admin: " .
                mysqli_error($koneksi)
            );
        }
    }


    // ==========================================
    // SETELAH BERHASIL
    // ==========================================
    header("Location: table.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Data</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-7">

            <div class="card shadow border-0">

                <div class="card-body p-4">
                    <form action="edit2.php?aksi=editguru" method="post">

                    <!-- ================================= -->
                    <!-- JUDUL -->
                    <!-- ================================= -->

                    <h3 class="fw-bold mb-4">
                        Edit Data
                    </h3>


                    <!-- ================================= -->
                    <!-- ROLE -->
                    <!-- ================================= -->

                    <div class="mb-3">

                        <label class="form-label">
                            Role
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= htmlspecialchars($role); ?>"
                            disabled
                        >

                    </div>


                    <!-- ================================= -->
                    <!-- SISWA -->
                    <!-- ================================= -->

                    <?php if ($role == 'siswa' && $siswa): ?>

                        <hr>

                        <h5 class="fw-bold mb-3">
                            Data Siswa
                        </h5>

                        <form method="POST">

                            <!-- NIS -->
                            <div class="mb-3">

                                <label class="form-label">
                                    NIS
                                </label>

                                <input
                                    type="text"
                                    name="nis"
                                    class="form-control"
                                    value="<?= htmlspecialchars($siswa['nis']); ?>"
                                    required
                                >

                            </div>


                            <!-- NAMA -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Nama
                                </label>

                                <input
                                    type="text"
                                    name="nama_siswa"
                                    class="form-control"
                                    value="<?= htmlspecialchars($siswa['nama_siswa']); ?>"
                                    required
                                >

                            </div>


                            <!-- KELAS -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Kelas
                                </label>

                                <input
                                    type="text"
                                    name="kelas"
                                    class="form-control"
                                    value="<?= htmlspecialchars($siswa['kelas']); ?>"
                                    required
                                >

                            </div>


                            <!-- JENIS KELAMIN -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Jenis Kelamin
                                </label>

                                <select
                                    name="jenis_kelamin"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        -- Pilih --
                                    </option>

                                    <option
                                        value="L"
                                        <?= $siswa['jenis_kelamin'] == 'L'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        Laki-laki
                                    </option>

                                    <option
                                        value="P"
                                        <?= $siswa['jenis_kelamin'] == 'P'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        Perempuan
                                    </option>

                                </select>

                            </div>


                            <!-- BUTTON -->
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Simpan Perubahan
                            </button>

                            <a
                                href="table.php"
                                class="btn btn-secondary"
                            >
                                Batal
                            </a>

                        </form>


                    <!-- ================================= -->
                    <!-- GURU -->
                    <!-- ================================= -->

                    <?php elseif ($role == 'guru' && $guru): ?>

                        <hr>

                        <h5 class="fw-bold mb-3">
                            Data Guru
                        </h5>

                        <form method="POST">

                            <!-- NIP -->
                            <div class="mb-3">

                                <label class="form-label">
                                    NIP
                                </label>

                                <input
                                    type="text"
                                    name="nip"
                                    class="form-control"
                                    value="<?= htmlspecialchars($guru['nip']); ?>"
                                    required
                                >

                            </div>


                            <!-- NAMA -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Nama
                                </label>

                                <input
                                    type="text"
                                    name="nama"
                                    class="form-control"
                                    value="<?= htmlspecialchars($guru['nama']); ?>"
                                    required
                                >

                            </div>


                            <!-- JENIS KELAMIN -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Jenis Kelamin
                                </label>

                                <select
                                    name="jenis_kelamin"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        -- Pilih --
                                    </option>

                                    <option
                                        value="L"
                                        <?= $guru['jenis_kelamin'] == 'L'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        Laki-laki
                                    </option>

                                    <option
                                        value="P"
                                        <?= $guru['jenis_kelamin'] == 'P'
                                            ? 'selected'
                                            : ''; ?>
                                    >
                                        Perempuan
                                    </option>

                                </select>

                            </div>


                            <!-- BUTTON -->
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Simpan Perubahan
                            </button>

                            <a
                                href="table.php"
                                class="btn btn-secondary"
                            >
                                Batal
                            </a>

                        </form>


                    <!-- ================================= -->
                    <!-- ADMIN -->
                    <!-- ================================= -->

                    <?php elseif ($role == 'admin'): ?>

                        <hr>

                        <h5 class="fw-bold mb-3">
                            Data Admin
                        </h5>

                        <form method="POST">

                            <!-- USERNAME -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Username
                                </label>

                                <input
                                    type="text"
                                    name="username"
                                    class="form-control"
                                    value="<?= htmlspecialchars($user['username']); ?>"
                                    required
                                >

                            </div>


                            <!-- PASSWORD -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Password Baru
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    placeholder="Kosongkan jika tidak ingin mengganti password"
                                >

                                <small class="text-muted">
                                    Kosongkan jika password lama tetap digunakan.
                                </small>

                            </div>


                            <!-- BUTTON -->
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Simpan Perubahan
                            </button>

                            <a
                                href="table.php"
                                class="btn btn-secondary"
                            >
                                Batal
                            </a>

                        </form>


                    <!-- ================================= -->
                    <!-- DATA TIDAK DITEMUKAN -->
                    <!-- ================================= -->

                    <?php else: ?>

                        <div class="alert alert-warning">

                            Data
                            <?= htmlspecialchars($role); ?>
                            tidak ditemukan.

                        </div>

                        <a
                            href="table.php"
                            class="btn btn-secondary"
                        >
                            Kembali
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>