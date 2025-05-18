<?php 
    include "proses/connect.php";
    $query = mysqli_query($conn, "SELECT * FROM users");
    while ($record = mysqli_fetch_array($query)){
        $result[] = $record;
    }
?>
<!-- content -->
<div class="col-lg-9 mt-2">
    <div class="card">
        <div class="card-header">
            User
        </div>
        <div class="card-body">
            
            <div class="row">
                <div class="col d-flex justify-content-end">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ModalTambahUser">Tambah User</button>
                </div>
            </div>
            <!-- Modal Tambah User Baru-->
            <div class="modal fade" id="ModalTambahUser" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah User</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="needs-validation" novalidate action="proses/proses_input.php" method="POST">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control" id="floatingInput" placeholder="Your Name" name="nama" required>
                                            <label for="floatingInput" name="nama">Nama</label>
                                            <div class="invalid-feedback">
                                                Masukkan nama.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-floating mb-3">
                                            <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="username" required>
                                            <label for="floatingInput">Username</label>
                                            <div class="invalid-feedback">
                                                    Masukkan username.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" aria-label="Default select example" name="level" required>
                                                <option selected hidden value="0">Pilih Level User</option>
                                                <option value="1">Admin/Guru</option>
                                                <option value="2">Orang Tua</option>
                                            </select>
                                            <label for="floatingInput">Level User</label>
                                            <div class="invalid-feedback">
                                                Pilih Level User.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="form-floating mb-3">
                                            <input type="number" class="form-control" id="floatingInput" placeholder="08xxxxx" name="nohp">
                                            <label for="floatingInput">No Hp</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="password" class="form-control" id="floatingPassword" placeholder="Password" disabled value="1234" name="password">
                                    <label for="floatingPassword">Password</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <textarea class="form-control" id="" style="height:100px" name="alamat"></textarea>
                                    <label for="floatingInput">Alamat</label>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="input_user_validate" value="satu">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- akhir modal user baru -->

            <?php
            foreach ($result as $row) {
            ?>
            <!-- Modal view -->
            <div class="modal fade" id="ModalView<?php echo $row['user_id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Data User</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="needs-validation" novalidate action="proses/proses_input.php" method="POST">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-floating mb-3">
                                                <input disabled type="text" class="form-control" id="floatingInput" placeholder="Your Name" name="nama" value="<?php echo $row['fullname']?>">
                                                <label for="floatingInput">Nama</label>
                                                <div class="invalid-feedback">
                                                    Masukkan nama.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-floating mb-3">
                                                <input disabled type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="username" value="<?php echo $row['username']?>">
                                                <label for="floatingInput">Username</label>
                                            </div>
                                            <div class="invalid-feedback">
                                                    Masukkan username.
                                            </div>
                                        </div>
                                    </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-floating mb-3">
                                            <select disabled class="form-select" aria-label="Defaul select example" required name="level" id="">
                                                <?php
                                                    $data = array("admin/guru", "Orang Tua");
                                                    foreach($data as $key => $value){
                                                        if($row['level'] == $key+1){
                                                            echo "<option selected value='$key'>$value</option>";
                                                        }else{
                                                            echo "<option value='$key'>$value</option>";
                                                        }
                                                    }
                                                ?>">
                                            </select>
                                            <label for="floatingInput">Level User</label>
                                            <div class="invalid-feedback">
                                                Pilih Level User.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="form-floating mb-3">
                                            <input disabled type="number" class="form-control" id="floatingInput" placeholder="08xxxxx" name="nohp" value="<?php echo $row['phone']?>">
                                            <label for="floatingInput">No Hp</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating">
                                    <textarea disabled class="form-control" id="" style="height:100px" name="alamat"><?php echo $row['alamat']?></textarea>
                                    <label for="floatingInput">Alamat</label>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- akhir modal view -->
            
            <!-- Modal Edit -->
            <div class="modal fade" id="ModalEdit<?php echo $row['user_id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-fullscreen-md-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Data User</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="needs-validation" novalidate action="proses/proses_edit.php" method="POST">
                                <input type="hidden" value="<?php echo $row['user_id']?>" name="id">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" id="floatingInput" placeholder="Your Name" name="nama"required value="<?php echo $row['fullname']?>">
                                                <label for="floatingInput">Nama</label>
                                                <div class="invalid-feedback">
                                                    Masukkan nama.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-floating mb-3">
                                                <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="username" required value="<?php echo $row['username']?>">
                                                <label for="floatingInput">Username</label>
                                            </div>
                                            <div class="invalid-feedback">
                                                    Masukkan username.
                                            </div>
                                        </div>
                                    </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-floating mb-3">
                                            <select class="form-select" aria-label="Defaul select example" required name="level" id="">
                                                <?php
                                                    $data = array("admin/guru", "Orang Tua");
                                                    foreach($data as $key => $value){
                                                        if($row['level'] == $key+1){
                                                            echo "<option selected value=".($key+1).">$value</option>";
                                                        }else{
                                                            echo "<option value=".($key+1).">$value</option>";
                                                        }
                                                    }
                                                ?>">
                                            </select>
                                            <label for="floatingInput">Level User</label>
                                            <div class="invalid-feedback">
                                                Pilih Level User.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-8">
                                        <div class="form-floating mb-3">
                                            <input type="number" class="form-control" id="floatingInput" placeholder="08xxxxx" name="nohp" value="<?php echo $row['phone']?>">
                                            <label for="floatingInput">No Hp</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating">
                                    <textarea class="form-control" id="" style="height:100px" name="alamat"><?php echo $row['alamat']?></textarea>
                                    <label for="floatingInput">Alamat</label>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" name="input_user_validate" value="satu">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- akhir modal edit -->

            <!-- Modal hapus -->
            <div class="modal fade" id="ModalDelete<?php echo $row['user_id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-md modal-fullscreen-md-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Data User</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="needs-validation" novalidate action="proses/proses_delete.php" method="POST">
                                <input type="hidden" value="<?php echo $row['user_id']?>" name="id">
                                    <div class="col lg-12">
                                        <?php 
                                        if($row['username'] == $_SESSION['username_deteksi']){
                                            echo "<div class='alert alert-danger'>Anda tidak menghapus akun sendiri</div>";
                                        }else{
                                            echo "Apakah anda yakin ingin menghapus data user? <b>$row[username]</b>";
                                        }
                                        ?>
                                    </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-danger" name="input_user_validate" value="satu" <?php echo ($row['username'] == $_SESSION['username_deteksi']) ? 'disabled' : '' ; ?>>Hapus Data</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- akhir modal hapus -->
            
            <!-- Modal reset password -->
            <div class="modal fade" id="ModalResetPassword<?php echo $row['user_id']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-md modal-fullscreen-md-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Data User</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form class="needs-validation" novalidate action="proses/proses_reset_password.php" method="POST">
                                <input type="hidden" value="<?php echo $row['user_id']?>" name="id">
                                    <div class="col lg-12">
                                        <?php 
                                        if($row['username'] == $_SESSION['username_deteksi']){
                                            echo "<div class='alert alert-danger'>Anda tidak dapat mereset password sendiri</div>";
                                        }else{
                                            echo "Apakah anda yakin ingin mereset password user ? <b>$row[username]</b> menjadi password bawaan sistem yaitu <b>admin123</b>";
                                        }
                                        ?>
                                    </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success" name="input_user_validate" value="satu" <?php echo ($row['username'] == $_SESSION['username_deteksi']) ? 'disabled' : '' ; ?>>Reset password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- akhir modal reset password -->
            <?php
            }
                if(empty($result)) {
                    echo "user tidak ada";
                }else{

                
            ?>
            <div class="table-responsive mt-2">
            <table class="table table-hover" id="example">
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Name Lengkap</th>
                        <th scope="col">Username</th>
                        <th scope="col">Level</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($result as $row) {
                    ?>
                    <tr>
                        <th scope="row">
                            <?Php echo $no++?>
                        </th>
                        <td>
                            <?Php echo $row['fullname']?>
                        </td>
                        <td>
                            <?Php echo $row['username']?>
                        </td>
                        <td>
                        <?php
                            if($row['level'] ==1){
                                echo "Guru";
                            }elseif($row['level'] ==2){
                                echo "Orang Tua";
                            }
                        ?>
                        </td>
                        <!-- <td><?Php echo $row['nip']?></td>
                        <td><?Php echo $row['position']?></td> -->
                        <td class="d-flex">
                            <button class="btn btn-info btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalView<?php echo $row['user_id']?>">
                                <i class="bi bi-eye"></i> 
                            </button>
                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalEdit<?php echo $row['user_id']?>">
                                <i class="bi bi-pencil-square"></i> 
                            </button>
                            <button class="btn btn-danger btn-sm me-1" data-bs-toggle="modal" data-bs-target="#ModalDelete<?php echo $row['user_id']?>">
                                <i class="bi bi-trash2"></i> 
                            </button>
                            <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#ModalResetPassword<?php echo $row['user_id']?>">
                                <i class="bi bi-shield-lock-fill"></i>
                            </button>
                        </td>
                    </tr>
                    <?php 
                    }
                    ?>
                </tbody>
            </table>
            </div>
            <?php 
                }
            ?>
        </div>
    </div>
</div>
<!-- end content -->
<script>
        let isLogin = true;
        let userType = '';

        function setUserType(type) {
            userType = type;
            document.getElementById('user-type').value = userType;
            document.getElementById('parent-btn').classList.toggle('btn-primary', userType === 'parent');
            document.getElementById('parent-btn').classList.toggle('btn-outline-primary', userType !== 'parent');
            document.getElementById('teacher-btn').classList.toggle('btn-primary', userType === 'teacher');
            document.getElementById('teacher-btn').classList.toggle('btn-outline-primary', userType !== 'teacher');
        }

        function toggleForm() {
            isLogin = !isLogin;
            document.getElementById('form-title').innerText = isLogin ? 'Login - Sistem Deteksi Perkembangan Anak' : 'Register - Sistem Deteksi Perkembangan Anak';
            document.getElementById('toggle-form').innerText = isLogin ? 'Belum punya akun? Register' : 'Sudah punya akun? Login';
            renderForm();
        }
        function renderForm() {
            const form = document.getElementById('auth-form');
            form.classList.remove('was-validated');
            form.innerHTML = '';

            if (isLogin) {
                form.innerHTML += `
                    <input type="hidden" id="user-type" name="user_type" required>
                    <div class="mb-3 position-relative">
                        <i class="fas fa-envelope form-icon"></i>
                        <input name="username" type="email" class="form-control form-input" placeholder="Email" required>
                        <div class="invalid-feedback">
                            Masukkan email yang valid.
                        </div>
                    </div>
                    <div class="mb-3 position-relative">
                        <i class="fas fa-lock form-icon"></i>
                        <input name="password" type="password" class="form-control form-input" id="password" placeholder="Password" required>
                        <div class="invalid-feedback">
                            Masukkan password yang valid.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" name="submit_validate" value="tes">Login</button>
                `;
            }else {
                form.innerHTML += `
                    <div class="mb-3 position-relative">
                        <i class="fas fa-user form-icon"></i>
                        <input type="text" class="form-control form-input" id="fullname" placeholder="Nama Lengkap" required>
                        <div class="invalid-feedback">
                            Masukkan nama lengkap.
                        </div>
                    </div>
                    <div class="mb-3 position-relative">
                        <i class="fas fa-envelope form-icon"></i>
                        <input type="email" class="form-control form-input" id="email" placeholder="Email" required>
                        <div class="invalid-feedback">
                            Masukkan email yang valid.
                        </div>
                    </div>
                    <div class="mb-3 position-relative">
                        <i class="fas fa-phone form-icon"></i>
                        <input type="tel" class="form-control form-input" id="phone" placeholder="Nomor Telepon" required>
                        <div class="invalid-feedback">
                            Masukkan nomor telepon yang valid.
                        </div>
                    </div>
                `;

                if (userType === 'parent') {
                    form.innerHTML += `
                        <div class="mb-3 position-relative">
                            <i class="fas fa-user-circle form-icon"></i>
                            <input type="text" class="form-control form-input" id="child-name" placeholder="Nama Anak" required>
                            <div class="invalid-feedback">
                                Masukkan nama anak.
                            </div>
                        </div>
                        <div class="mb-3 position-relative">
                            <input type="text" class="form-control form-input" id="child-nis" placeholder="NIS Anak" required>
                            <div class="invalid-feedback">
                                Masukkan NIS anak.
                            </div>
                        </div>
                    `;
                } else if (userType === 'teacher') {
                    form.innerHTML += `
                        <div class="mb-3 position-relative">
                            <input type="text" class="form-control form-input" id="nip" placeholder="NIP" required>
                            <div class="invalid-feedback">
                                Masukkan NIP.
                            </div>
                        </div>
                        <div class="mb-3 position-relative">
                            <input type="text" class="form-control form-input" id="position" placeholder="Jabatan" required>
                            <div class="invalid-feedback">
                                Masukkan jabatan.
                            </div>
                        </div>
                    `;
                }

                form.innerHTML += `
                    <div class="mb-3 position-relative">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" class="form-control form-input" id="password" placeholder="Password" required>
                        <div class="invalid-feedback">
                            Masukkan password yang valid.
                        </div>
                    </div>
                    <div class="mb-3 position-relative">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" class="form-control form-input" id="confirm-password" placeholder="Konfirmasi Password" required>
                        <div class="invalid-feedback">
                            Masukkan konfirmasi password yang valid.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                `;
            }
        }

        document.getElementById('toggle-form').addEventListener('click', toggleForm);

        document.getElementById('auth-form').addEventListener('submit', function(event) {
            if (!this.checkValidity() || userType === '') {
                event.preventDefault();
                event.stopPropagation();
                if (userType === '') {
                    alert('Silakan pilih tipe user (Guru atau Orang Tua) sebelum login.');
                }
            }
            this.classList.add('was-validated');
        });

        renderForm();
        
</script>
