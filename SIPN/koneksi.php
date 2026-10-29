<?php
$username = "root";
$password = "";
$hostname = "localhost";
$data = "sekolah_sangkuriang1";

$koneksi = mysqli_connect($hostname, $username, $password, $data);

if ($koneksi) {
   // echo "koneksi berhasil";//
}
else{
    echo "koneksi gagal";
}
?>