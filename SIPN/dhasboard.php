<?php
require_once 'navbar.php'; // Memanggil navbar & validasi session
require_once 'koneksi.php';

$role     = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? '';
$user_id  = $_SESSION['user_id'] ?? 0;

// Ambil Nama Lengkap sesuai Role untuk disapa di Dashboard
$nama_lengkap = $username;

if ($role == 'siswa') {
    $q = mysqli_query($koneksi, "SELECT nama FROM siswa WHERE user_id = '$user_id'");
    if ($data = mysqli_fetch_assoc($q)) {
        $nama_lengkap = $data['nama'];
    }
} elseif ($role == 'guru') {
    $q = mysqli_query($koneksi, "SELECT nama FROM guru WHERE user_id = '$user_id'");
    if ($data = mysqli_fetch_assoc($q)) {
        $nama_lengkap = $data['nama'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data siswa </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">


            <hr class="my-4">
        </div>
    </div>
</div>

</body>
</html>