<?php
// Wajib ditambahin biar PHP ga langsung crash kalau ada error dari MySQL
mysqli_report(MYSQLI_REPORT_OFF);

require_once 'koneksi.php'; // Koneksi harus paling atas
?>

<head>
    <link rel="stylesheet" href="style1.css">
</head>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nis           = $_POST['nis'] ?? '';
    $nama          = $_POST['nama'] ?? '';
    $kelas         = $_POST['kelas'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $username      = $_POST['username'] ?? '';
    $password      = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $role          = $_POST['role'] ?? '';

    // Mulai Database Transaction agar aman (jika salah satu gagal, dibatalkan semua)
    mysqli_begin_transaction($koneksi);

    try {
        // 1. Insert ke tabel users
        $sql_users = "INSERT INTO users (username, password, role, Creat_id) 
                      VALUES ('$username', '$password', '$role', NOW())";
        $query_users = mysqli_query($koneksi, $sql_users);

        if (!$query_users) {
            throw new Exception("Gagal menyimpan ke tabel users: " . mysqli_error($koneksi));
        }

        // Ambil ID user yang barusan dibuat
        $id_terakhir = mysqli_insert_id($koneksi);

        // 2. Insert ke tabel siswa 
        // (Pastikan nama kolom 'kelamin' atau 'jenis_kelamin' di database sesuai dengan tulisan di bawah)
        $sql_siswa = "INSERT INTO siswa (nis, nama_siswa, kelas, kelamin, user_id) 
                      VALUES ('$nis', '$nama', '$kelas', '$jenis_kelamin', '$id_terakhir')";
        $query_siswa = mysqli_query($koneksi, $sql_siswa);

        if (!$query_siswa) {
            throw new Exception("Gagal menyimpan ke tabel siswa: " . mysqli_error($koneksi));
        }

        // Jika dua-duanya sukses, simpan permanen (Commit)
        mysqli_commit($koneksi);

        echo "<script>
                alert('Mantap, data berhasil disimpan bre!'); 
                window.location.href='tabel2.php';
              </script>";
        exit;

    } catch (Exception $e) {
        // Jika ada error di tengah jalan, batalkan semua perubahan (Rollback)
        mysqli_rollback($koneksi);
        
        $pesan_error = addslashes($e->getMessage());
        echo "<script>
                alert('$pesan_error'); 
                window.location.href='form2.php';
              </script>";
        exit;
    }

} else {
    header("Location: tabel2.php");
    exit;
}
?>