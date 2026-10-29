<?php

session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}


if ($_SESSION['role'] == 'admin') {

    header("Location: admin/dhasboard.php");
    exit;

} elseif ($_SESSION['role'] == 'guru') {

    header("Location: guru/dhasboard.php");
    exit;

} elseif ($_SESSION['role'] == 'siswa') {

    header("Location: siswa/dhashboard.php");
    exit;

} else {

    session_destroy();

    header("Location: login.php");
    exit;
}
?>