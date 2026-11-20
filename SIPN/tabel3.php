<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek status login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role           = strtolower(trim($_SESSION['role'] ?? ''));
$username_login = $_SESSION['username'] ?? '';
$username_aman  = mysqli_real_escape_string($koneksi, $username_login);

// Ambil data user & relasi ke siswa/guru
$sql = "SELECT users.*,
        COALESCE(siswa.nama, guru.nama) AS nama
        FROM users
        LEFT JOIN siswa ON users.id = siswa.user_id
        LEFT JOIN guru ON users.id = guru.user_id
        ORDER BY users.id DESC";

$hasil = mysqli_query($koneksi, $sql);

$data1 = []; 
if ($hasil && mysqli_num_rows($hasil) > 0) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        $data1[] = $row;
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Siswa & Users</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">

<!-- Hapus kodingan nav lama di tabel3.php, lalu ganti dengan ini: -->
<?php include 'navbar.php'; ?>
<!-- KONTEN UTAMA HALAMAN -->
<div class="container mt-4 mb-5">
    <div class="container center">
        <h1 class="fw-normal display-4">Data Siswa</h1>
        <hr class="border border-dark border-2 opacity-100">
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th scope="col">No</th>
              <th scope="col">Username</th>
              <th scope="col">Role</th>
              <th scope="col">Tanggal Dibuat</th>
              <th scope="col" style="width: 15%;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1; 
            if (!empty($data1)) {
                foreach ($data1 as $user) {
            ?>
              <tr>
                <th scope="row"><?php echo $no++; ?></th>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><span class="badge bg-info text-dark"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span></td>
                <td><?php echo htmlspecialchars($user['Creat_id'] ?? $user['created_at'] ?? '-'); ?></td>
                <td>
                  <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                  <a href="aksi_hapus.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Delete</a>
                </td>
              </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='5' class='text-center text-muted py-3'>Tidak ada data.</td></tr>";
            }
            ?>
          </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>