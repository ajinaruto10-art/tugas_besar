<?php
session_start();
require_once 'koneksi.php';

// 1. Cek Autentikasi (Apakah sudah login?)
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// 2. Cek Otorisasi (Apakah perannya Guru atau Admin?)
if ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin') {
    echo "<script>
            alert('Akses Ditolak! Anda tidak memiliki izin untuk menambah data nilai.');
            window.location.href = 'menampilkan_data.php';
          </script>";
    exit;
}

// --- JIKA LOLOS OTORISASI, BARU PROSES INSERT DIJALANKAN ---
if (isset($_POST['simpan'])) {
    $mapel_id           = $_POST['mapel_id'];
    $nilai_kkm          = $_POST['nilai_kkm'];
    $nilai_pengetauan   = $_POST['nilai_pengetauan'];
    $nilai_keterampilan = $_POST['nilai_keterampilan'];
    $keterangan         = $_POST['keterangan'];

    $query = "INSERT INTO nilail_raport (mapel_id, nilai_kkm, nilial_pengetauan, nilail_keterampilan, keterangan) 
              VALUES ('$mapel_id', '$nilai_kkm', '$nilai_pengetauan', '$nilai_keterampilan', '$keterangan')";

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        echo "<script>
                alert('Data nilai berhasil disimpan!');
                window.location.href = 'menampilkan_data.php';
              </script>";
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
} else {
    header("Location: form_nilai.php");
    exit;
}
?>