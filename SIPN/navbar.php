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
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
  <div class="container">
    <!-- Brand / Judul Navbar -->
    <a class="navbar-brand fw-bold" href="tabel2.php">SIPN SMK Sangkuriang 1</a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        
        <!-- Dashboard / Data untuk Semua Role -->
        <li class="nav-item">
          <a class="nav-link text-white fw-semibold" href="tabel2.php">Data Nilai</a>
        </li>
        
        <?php if ($role === 'admin'): ?>
            <!-- Menu Khusus ADMIN -->
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="tabel3.php">Kelola User</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="mapel.php">Mata Pelajaran</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="tambah_nilai.php">Kelola Nilai</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="daftar_guru.php">Daftar Guru</a>
            </li>

        <?php elseif ($role === 'guru'): ?>
            <!-- Menu Khusus GURU -->
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="mapel.php">Daftar Mapel</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="tambah_nilai.php">Kelola Nilai</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="daftar_guru.php">Daftar Guru</a>
            </li>

        <?php elseif ($role === 'siswa'): ?>
            <!-- Menu Khusus SISWA -->
            <li class="nav-item">
              <a class="nav-link text-white fw-semibold" href="tabel2.php">Lihat Nilai Rapor</a>
            </li>
        <?php endif; ?>

      </ul>
      
      <!-- Info Pengguna Login -->
      <span class="navbar-text me-3 text-white small">
        <?= htmlspecialchars($username); ?> (<strong><?= ucfirst($role); ?></strong>)
      </span>
      
      <!-- Tombol Pengaturan & Logout -->
      <div class="d-flex gap-2">
        <a href="pengaturan.php" class="btn btn-light btn-sm fw-bold text-primary">Pengaturan</a>
        <a href="logout.php" class="btn btn-danger btn-sm fw-bold">Logout</a>
      </div>
    </div>
  </div>
</nav>