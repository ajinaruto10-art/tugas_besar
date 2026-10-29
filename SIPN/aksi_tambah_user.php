<?php
mysqli_report(MYSQLI_REPORT_OFF); 
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username'] ?? '');
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $role     = mysqli_real_escape_string($koneksi, $_POST['role'] ?? '');

    // Insert ke tabel users
    $sql_users = "INSERT INTO users (username, password, role, created_at) 
                  VALUES ('$username', '$password', '$role', NOW())";
    $query_users = mysqli_query($koneksi, $sql_users);

    if ($query_users) {
        $id_terakhir = mysqli_insert_id($koneksi);
        $query_profil = true;

        if ($role == 'siswa') {
            $nis           = mysqli_real_escape_string($koneksi, $_POST['nis'] ?? '');
            $nama          = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
            $kelas         = mysqli_real_escape_string($koneksi, $_POST['kelas'] ?? '');
            $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');

            $sql_siswa = "INSERT INTO siswa (nis, nama, kelas, jenis_kelamin, user_id) 
                          VALUES ('$nis', '$nama', '$kelas', '$jenis_kelamin', '$id_terakhir')";
            $query_profil = mysqli_query($koneksi, $sql_siswa);

        } elseif ($role == 'guru') {
            $nip           = mysqli_real_escape_string($koneksi, $_POST['nip'] ?? '');
            $nama          = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
            $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');

            $sql_guru = "INSERT INTO guru (nip, nama, jenis_kelamin, user_id) 
                         VALUES ('$nip', '$nama', '$jenis_kelamin', '$id_terakhir')";
            $query_profil = mysqli_query($koneksi, $sql_guru);
        }

        if ($query_profil) {
            echo "<script>
                    alert('Berhasil! Data $role tersimpan.'); 
                    window.location.href='tabel3.php';
                  </script>";
            exit; 
        } else {
            $error_profil = addslashes(mysqli_error($koneksi));
            echo "<script>
                    alert('Gagal menyimpan profil $role: $error_profil');
                    window.location.href='tabel3.php';
                  </script>";
            exit;
        }
    } else {
        $error_users = addslashes(mysqli_error($koneksi));
        echo "<script>
                alert('Gagal menyimpan data ke tabel users: $error_users'); 
                window.location.href='tabel3.php';
              </script>";
        exit;
    }
} else {
    header("Location: tabel3.php");
    exit;
}
?>