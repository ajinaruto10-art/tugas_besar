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

    // Insert ke tabel users
    $sql_users = "INSERT INTO users (username, password, role, Creat_id) 
                  VALUES ('$username', '$password', '$role', NOW())";
    $query_users = mysqli_query($koneksi, $sql_users);

    if ($query_users) {
        $id_terakhir = mysqli_insert_id($koneksi);

        // Insert ke tabel siswa pake ID user yang barusan dibuat
        $sql_siswa = "INSERT INTO siswa (nis, nama_siswa, kelas,kelamin, user_id) 
                      VALUES ('$nis', '$nama', '$kelas', '$jenis_kelamin', '$id_terakhir')";
        $query_siswa = mysqli_query($koneksi, $sql_siswa);

        if ($query_siswa) {
            // Kalau sukses, tampilin alert terus STOP script pake exit biar ga bablas
            echo "<script>
                    alert('Mantap, data berhasil disimpan bre!'); 
                    window.location.href='tabel2.php';
                  </script>";
            exit;
        } else {
            // Pake addslashes biar tanda kutip dari error MySQL ga ngerusak Javascript
            $error_siswa = addslashes(mysqli_error($koneksi));
            echo "<script>alert('Gagal menyimpan data siswa: $error_siswa'); window.location.href='form2.php';</script>";
            exit;
        }
    } else {
        $error_users = addslashes(mysqli_error($koneksi));
        echo "<script>
                alert('Gagal menyimpan data kedalam tabel users: $error_users'); 
                window.location.href='tabel2.php';
              </script>";
        exit;
    }

    echo "NIS: " . htmlspecialchars($nis) . "<br>";
    echo "Nama: " . htmlspecialchars($nama) . "<br>";
    echo "Kelas: " . htmlspecialchars($kelas) . "<br>";
    echo "Jenis Kelamin: " . htmlspecialchars($jenis_kelamin) . "<br>";
    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "Password: " . htmlspecialchars($password) . "<br>";
    echo "Role: " . htmlspecialchars($role) . "<br>";
} else {
    header("Location: tabel2.php");
    exit;
}
?>