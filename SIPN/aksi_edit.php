<?php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: tabel3.php");
    exit;
}

$id       = mysqli_real_escape_string($koneksi, $_POST['id'] ?? '');
$username = mysqli_real_escape_string($koneksi, $_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($id)) {
    die("Error: ID user tidak valid!");
}

$queryUser = mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id'");
if (!$queryUser || mysqli_num_rows($queryUser) == 0) {
    die("User tidak ditemukan.");
}

$user = mysqli_fetch_assoc($queryUser);
$role = $user['role'];

if (!empty($password)) {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $query = mysqli_query($koneksi, "UPDATE users SET username='$username', password='$passwordHash' WHERE id='$id'");
} else {
    $query = mysqli_query($koneksi, "UPDATE users SET username='$username' WHERE id='$id'");
}

if (!$query) {
    die("Gagal update users: " . mysqli_error($koneksi));
}

if ($role == 'siswa') {
    $nis           = mysqli_real_escape_string($koneksi, $_POST['nis'] ?? '');
    $nama_siswa    = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $kelas         = mysqli_real_escape_string($koneksi, $_POST['kelas'] ?? '');
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');

    $querySiswa = mysqli_query($koneksi, "UPDATE siswa SET 
        nis='$nis', 
        nama='$nama_siswa', 
        kelas='$kelas', 
        jenis_kelamin='$jenis_kelamin' 
        WHERE user_id='$id'");

    if (!$querySiswa) {
        die("Gagal update siswa: " . mysqli_error($koneksi));
    }

} elseif ($role == 'guru') {
    $nip           = mysqli_real_escape_string($koneksi, $_POST['nip'] ?? '');
    $nama          = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');

    $queryGuru = mysqli_query($koneksi, "UPDATE guru SET 
        nip='$nip', 
        nama='$nama', 
        jenis_kelamin='$jenis_kelamin' 
        WHERE user_id='$id'");

    if (!$queryGuru) {
        die("Gagal update guru: " . mysqli_error($koneksi));
    }
}

echo "<script>
        alert('Data berhasil diperbarui!'); 
        window.location.href='tabel3.php';
      </script>";
exit;
?>