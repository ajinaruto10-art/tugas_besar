<?php
// Pastikan session dimulai terlebih dahulu sebelum dihapus
session_start();

// 1. Kosongkan semua variabel session
$_SESSION = array();

// 2. Jika menggunakan cookie session, hapus cookie-nya juga
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session
session_destroy();

// 4. Redirect langsung ke halaman login
header("Location: login.php");
exit; // WAJIB ada agar eksekusi script langsung berhenti
?>