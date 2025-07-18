<?php 
    include "proses/connect.php";
    $query = mysqli_query($conn, "SELECT s.*, u.fullname as parent_name 
                                FROM students s 
                                LEFT JOIN users u ON s.parent_id = u.user_id");
    $result = [];
    while ($record = mysqli_fetch_array($query)){
        $result[] = $record;
    }
?>
<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header">
            Daftar Siswa
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col d-flex justify-content-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalTambahSiswa">Tambah Siswa</button>
                </div>
            </div>

            <!-- Modal Tambah Siswa -->
<div class="modal fade" id="ModalTambahSiswa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Siswa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate action="proses/proses_input_siswa.php" method="POST">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="namaSiswa" placeholder="Nama Siswa" name="nama" required>
                                <label for="namaSiswa">Nama Siswa</label>
                                <div class="invalid-feedback">
                                    Masukkan nama
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="kelas" placeholder="kelas" name="kelas" required>
                                <label for="kelas">Kelas</label>
                                <div class="invalid-feedback">
                                    Pilih kelas
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" aria-label="Pilih Jenis Kelamin" name="jenis_kelamin" required>
                                    <option selected hidden value="">Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                                <div class="invalid-feedback">
                                    Pilih Jenis Kelamin
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" aria-label="Pilih Orang Tua" name="ortu_id" required>
                                    <option selected hidden value="">Pilih Orang Tua</option>
                                    <?php 
                                    // Query untuk mendapatkan daftar orang tua
                                    $query_ortu = mysqli_query($conn, "SELECT user_id, fullname FROM users WHERE level = 2");
                                    while ($ortu = mysqli_fetch_array($query_ortu)) {
                                        echo "<option value='{$ortu['user_id']}'>{$ortu['fullname']}</option>";
                                    }
                                    ?>
                                </select>
                                <label for="ortu">Orang Tua</label>
                                <div class="invalid-feedback">
                                    Pilih Orang Tua
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" name="usia" value="<?php echo $row['usia']; ?>">
                        <label for="usia">Usia</label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" name="input_siswa_validate" value="satu">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Akhir Modal Tambah Siswa -->

            <?php
            // Modal View, Edit, Delete akan dibuat serupa dengan modal tambah
            // Contoh untuk modal view:
            foreach ($result as $row) {
            ?>
            <!-- Modal View Siswa -->
            <div class="modal fade" id="ModalView<?php echo $row['student_id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Detail Siswa</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="needs-validation" novalidate>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-floating mb-3">
                                            <input disabled type="text" class="form-control" value="<?php echo $row['name']?>">
                                            <label>Nama Siswa</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-floating mb-3">
                                            <input disabled type="text" class="form-control" value="<?php echo $row['class']?>">
                                            <label>Kelas</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-floating mb-3">
                                            <input disabled type="text" class="form-control" value="<?php echo $row['gender']?>">
                                            <label>Jenis Kelamin</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-floating mb-3">
                                            <input disabled type="text" class="form-control" value="<?php echo $row['parent_name']?>">
                                            <label>Nama Orang Tua</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-3">
                                <input disabled type="number" class="form-control" name="usia" value="<?php echo $row['usia']; ?>">
                                    <label>Usia</label>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
<!-- modal edit siswa -->
            <div class="modal fade" id="ModalEdit<?php echo $row['student_id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Siswa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate action="proses/proses_edit_siswa.php" method="POST">
                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']?>">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="namaSiswa" placeholder="Nama Siswa" name="nama" required value="<?php echo $row['name']?>">
                                <label for="namaSiswa">Nama Siswa</label>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="kelas" placeholder="kelas" name="kelas" value="<?php echo $row['class']?>" required>
                                <label for="kelas">Kelas</label>
                                <div class="invalid-feedback">
                                    Pilih kelas
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" aria-label="Pilih Jenis Kelamin" name="jenis_kelamin" required>
                                    <option selected hidden value="">Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                                <label for="jenis_kelamin">Jenis Kelamin</label>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" aria-label="Pilih Orang Tua" name="ortu_id" required>
                                    <?php 
                                    $query_ortu = mysqli_query($conn, "SELECT user_id, fullname FROM users WHERE level = 2");
                                    while ($ortu = mysqli_fetch_array($query_ortu)) {
                                        $selected = ($row['parent_id'] == $ortu['user_id']) ? 'selected' : '';
                                        echo "<option value='{$ortu['user_id']}' $selected>{$ortu['fullname']}</option>";
                                    }
                                    ?>
                                </select>
                                <label for="ortu">Orang Tua</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                    <input type="number" class="form-control" name="usia" value="<?php echo $row['usia']; ?>">
                        <label>Usia</label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" name="edit_siswa_validate" value="satu">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Akhir Modal Edit Siswa -->

<!-- Modal Hapus Siswa -->
<div class="modal fade" id="ModalDelete<?php echo $row['student_id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Data Siswa</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="needs-validation" novalidate action="proses/proses_delete_siswa.php" method="POST">
                    <input type="hidden" name="student_id" value="<?php echo $row['student_id']?>">
                    <div class="col-lg-12">
                        Apakah anda yakin ingin menghapus data siswa? <b><?php echo $row['name']?></b>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-danger" name="hapus_siswa_validate" value="satu">Hapus Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
            <!-- Akhir Modal View Siswa -->
            <?php } ?>

            <!-- Tabel Daftar Siswa -->
            <?php if(empty($result)) { ?>
                <div class="alert alert-info">Tidak ada data siswa</div>
            <?php } else { ?>
            <div class="table-responsive mt-2">
                <table class="table table-hover" id="example">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Nama</th>
                            <th scope="col">Kelas</th>
                            <th scope="col">Orang Tua</th>
                            <th scope="col">Jenis Kelamin</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($result as $row) {
                        ?>
                        <tr>
                            <th scope="row"><?php echo $no++?></th>
                            <td><?php echo $row['name']?></td>
                            <td><?php echo $row['class']?></td>
                            <td><?php echo $row['parent_name']?></td>
                            <td><?php echo $row['gender']?></td>
                            <td class="d-flex">
                                <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalView<?php echo $row['student_id']?>">
                                    <i class="bi bi-eye"></i> 
                                </button>
                                <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalEdit<?php echo $row['student_id']?>">
                                    <i class="bi bi-pencil-square"></i> 
                                </button>
                                <button class="btn btn-danger btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalDelete<?php echo $row['student_id']?>">
                                    <i class="bi bi-trash2"></i> 
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>
        </div>
    </div>
</div>