<?php 
include 'navbar.php'; ?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="card-title mb-0 fw-bold">Form Tambah user 2</h4>
                </div>
                
                <div class="card-body">
                    <form action="aksi_tambah_user.php" method="POST">
                        
                        <h5 class="fw-bold mb-4">Data Profil User 2</h5>
                        
                        <div class="mb-3">
                            <label for="nis" class="form-label">
                                Nomor Induk Siswa <span class="text-muted fw-normal ms-2">| Contoh: 12345678</span>
                            </label>
                            <input type="text" class="form-control" id="nis" name="nis">
                        </div>

                        <div class="mb-3">
                            <label for="nama_siswa" class="form-label">
                                Nama Lengkap<span class="text-muted fw-normal ms-2">| Contoh: Ujang A</span>
                            </label>
                            <input type="text" class="form-control" id="nama_siswa" name="nama_siswa">
                        </div>

                        <div class="mb-3">
                            <label for="kelas" class="form-label">
                                Kelas Siswa <span class="text-muted fw-normal ms-2">| Contoh: XI-RPL-1</span>
                            </label>
                            <input type="text" class="form-control" id="kelas" name="kelas">
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block">Jenis Kelamin:</label>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="laki" value="L">
                                <label class="form-check-label" for="laki">Laki-laki</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="perempuan" value="p">
                                <label class="form-check-label" for="perempuan">Perempuan</label>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="fw-bold mb-4">Data Kredensial Akun (Tabel Users)</h5>
 <div class="mb-3">
                            <label for="username" class="form-label">
                                Username <span class="text-muted fw-normal ms-2">| Contoh: ajii234</span>
                            </label>
                            <input type="text" class="form-control" id="username" name="username">
                        </div>


                       <div class="mb-3">
                            <label for="password" class="form-label">
                                password <span class="text-muted fw-normal ms-2">| Contoh: 1212123</span>
                            </label>
                            <input type="password" class="form-control" id="password" name="password">
                            <input type ="hidden" name="role" value="siswa">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="Kirim" class="btn btn-primary">Kirim</button>
                            <a href="#" class="btn btn-secondary">Kembali</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>


<?php if (isset($_POST['Kirim'])) : ?>

    <?php
    $nis           = $_POST['nis'] ?? '';
    $nama          = $_POST['nama'] ?? '';
    $kelas         = $_POST['kelas'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $username      = $_POST['username'] ?? '';
    $password      = $_POST['password'] ?? '';

    if (
        empty($nis) || 
        empty($nama) || 
        empty($kelas) || 
        empty($jenis_kelamin) || 
        empty($username) || 
        empty($password)
    ) : ?>
        <script>
            alert('Semua isi data dalam form wajib diisi!');
        </script>
    <?php else : ?>
        <h1>Output Dari FORM</h1>

        <?php
        echo "nis = " . $nis . "<br>";
        echo "nama = " . $nama . "<br>";
        echo "kelas = " . $kelas . "<br>";
        echo "jenis_kelamin = " . $jenis_kelamin . "<br><br><br>"; ?> 

    <?php endif ; ?>
    <?php endif ; ?>