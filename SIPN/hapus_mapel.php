<?php
require_once __DIR__ . '/koneksi.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $id = mysqli_real_escape_string($koneksi, $id);
    
    // Eksekusi query hapus
    $query = "DELETE FROM mapel WHERE id = '$id'";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: mapel.php?status=success_hapus");
        exit();
    } else {
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }
} else {
    header("Location: mapel.php");
    exit();
}