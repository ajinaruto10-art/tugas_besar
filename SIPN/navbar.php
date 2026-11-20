<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower(trim($_SESSION['role'] ?? ''));
$username = $_SESSION['username'] ?? 'User';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
  <div class="container">
    <!-- Brand / Judul Navbar -->
    <a class="navbar-brand fw-bold" href="daftar_user2.php">
      <i class="bi bi-mortarboard-fill me-2"></i>SIPN - SMK Sangkuriang 1
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">

        <?php if ($role === 'admin'): ?>
            <!-- Home Admin (Langsung ke Daftar User) -->
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="daftar_user2.php">Home</a>
            </li>

            <!-- Dropdown USER -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                User
              </a>
              <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="daftar_user2.php">Daftar User</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="tambah_admin.php">Tambah Admin</a></li>
                <li><a class="dropdown-item" href="tambah_guru.php">Tambah Guru</a></li>
                <li><a class="dropdown-item" href="tambah_siswa.php">Tambah Siswa</a></li>
              </ul>
            </li>

            <!-- Dropdown SISWA -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Siswa
              </a>
              <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="tabel_siswa.php">Tabel Seluruh Siswa</a></li>
                <li><a class="dropdown-item" href="tambah_siswa.php">Tambah Siswa</a></li>
              </ul>
            </li>

            <!-- Dropdown GURU -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Guru
              </a>
              <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="daftar_guru.php">Daftar Guru</a></li>
                <li><a class="dropdown-item" href="tambah_guru.php">Tambah Guru</a></li>
              </ul>
            </li>

            <!-- Dropdown MAPEL -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Mapel
              </a>
              <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="mapel.php">Mata Pelajaran</a></li>
                <li><a class="dropdown-item" href="tambah_mapel.php">Tambah Mapel</a></li>
              </ul>
            </li>

            <!-- Dropdown MENGAJAR -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Mengajar
              </a>
              <ul class="dropdown-menu shadow">
                <li><a class="dropdown-item" href="daftar_mengajar.php">Daftar Mengajar</a></li>
                <li><a class="dropdown-item" href="tambah_mengajar.php">Tambah Mengajar</a></li>
              </ul>
            </li>

        <?php elseif ($role === 'guru'): ?>
            <!-- Menu Guru -->
            <li class="nav-item"><a class="nav-link text-white fw-semibold" href="daftar_guru.php">Home</a></li>
            <li class="nav-item"><a class="nav-link text-white fw-semibold" href="daftar_kelas_guru.php">Nilai Siswa</a></li>

        <?php elseif ($role === 'siswa'): ?>
            <!-- Menu Siswa -->
            <li class="nav-item"><a class="nav-link text-white fw-semibold" href="tabel2.php">Lihat Nilai Rapor</a></li>
        <?php endif; ?>

      </ul>

      <!-- Info Pengguna & Pengaturan Akun -->
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle btn btn-outline-light text-white px-3 py-1" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear-fill me-1"></i> <?= htmlspecialchars($username); ?> (<?= ucfirst($role); ?>)
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle me-2"></i> Profil Saya</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger fw-semibold" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>