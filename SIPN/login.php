<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, langsung arahkan sesuai role
if (!empty($_SESSION['login'])) {
    $role_sesi = strtolower(trim((string)($_SESSION['role'] ?? '')));
    if ($role_sesi === 'admin') {
        header("Location: daftar_mengajar.php");
    } elseif ($role_sesi === 'guru') {
        header("Location:dashboard_guru.php");
    } else {
        header("Location: tabel2.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPN Login - SMK Sangkuriang 1 Cimahi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            background-color: #ffffff;
        }
        .login-header {
            background-color: #2c3846;
            color: #ffffff;
            padding: 26px 20px;
            text-align: center;
        }
        .login-header .brand-title {
            font-size: 1.55rem;
            font-weight: 700;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .login-header .brand-subtitle {
            font-size: 0.9rem;
            color: #d6dde5;
            margin-bottom: 0;
        }
        .form-control {
            padding-top: 10px;
            padding-bottom: 10px;
            border-color: #ced4da;
        }
        .input-group-text {
            background-color: #ffffff;
            color: #6c757d;
            border-color: #ced4da;
        }
        .btn-login-dark {
            background-color: #2c3846;
            border-color: #2c3846;
            color: #ffffff;
            padding: 10px 16px;
            font-weight: 600;
        }
        .btn-login-dark:hover,
        .btn-login-dark:focus {
            background-color: #1f2833;
            border-color: #1f2833;
            color: #ffffff;
        }
        .register-link {
            color: #2c3846;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link:hover {
            text-decoration: underline;
        }
        .footer-note {
            font-size: 0.8rem;
            color: #8c96a0;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="login-header">
        <div class="brand-title">
            <i class="bi bi-mortarboard-fill"></i>
            <span>SIPN Login</span>
        </div>
        <p class="brand-subtitle">Sistem Informasi Pengelolaan Nilai</p>
    </div>

    <div class="card-body p-4">
        <form action="aksi_login.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold text-dark">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input
                        type="text"
                        class="form-control border-start-0"
                        id="username"
                        name="username"
                        placeholder="Masukkan username..."
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold text-dark">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input
                        type="password"
                        class="form-control border-start-0"
                        id="password"
                        name="password"
                        placeholder="Masukkan password..."
                        required
                    >
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-login-dark w-100 mb-4">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>

            <div class="text-center mb-3">
                <div class="small text-muted mb-1">Belum punya akun siswa?</div>
                <a href="register.php" class="register-link small">
                    <i class="bi bi-person-plus-fill me-1"></i>Daftar Akun Siswa di Sini
                </a>
            </div>

            <div class="text-center footer-note pt-2">
                &copy; 2026 SMK Sangkuriang 1 Cimahi
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>