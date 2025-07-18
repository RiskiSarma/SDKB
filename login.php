<?php  
    // session_start();
    if(!empty($_SESSION['username_deteksi'])) {
        header('Location:dashboard');
    }
?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Sistem Deteksi Keterlambatan Belajar Siswa</title>
    <style>
        .card-header {
            text-align: center;
        }
        .form-group {
        position: relative;
        }

        .form-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: gray;
            pointer-events: none;
        }

        .form-input {
            padding-left: 35px; /* Sesuaikan dengan ukuran ikon */
        }

        .invalid-feedback {
            position: absolute;
            bottom: -20px; /* Geser feedback ke bawah */
            left: 0;
            font-size: 0.875rem;
        }

    </style>
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light p-4">
        <div class="card w-100" style="max-width: 400px;">
            <div class="card-header">
                <h4 id="form-title">Login - Sistem Deteksi Keterlambatan Belajar Siswa</h4>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex">
                    <button id="parent-btn" class="btn btn-outline-primary w-50 me-2" onclick="setUserType('parent')">
                        <i class="fas fa-user-circle me-2"></i>Orang Tua
                    </button>
                    <button id="teacher-btn" class="btn btn-outline-primary w-50" onclick="setUserType('teacher')">
                        <i class="fas fa-graduation-cap me-2"></i>Guru
                    </button>
                </div>
                <form id="auth-form" class="needs-validation" novalidate action="proses/proses_login.php" method="POST">
                    <input type="hidden" id="user-type" name="user_type" required>
                    <div class="mb-3 position-relative">
                        <i class="fas fa-envelope form-icon"></i>
                        <input type="email" class="form-control form-input" id="email" name="username" placeholder="Email" required>
                        <div class="invalid-feedback">
                            Masukkan email yang valid.
                        </div>
                    </div>
                    <div class="mb-3 position-relative">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" class="form-control form-input" id="password" name="password" placeholder="Password" required>
                        <div class="invalid-feedback">
                            Masukkan password yang valid.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" name="submit_validate" value="tes">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let userType = '';

        function setUserType(type) {
            userType = type;
            document.getElementById('user-type').value = userType;
            document.getElementById('parent-btn').classList.toggle('btn-primary', userType === 'parent');
            document.getElementById('parent-btn').classList.toggle('btn-outline-primary', userType !== 'parent');
            document.getElementById('teacher-btn').classList.toggle('btn-primary', userType === 'teacher');
            document.getElementById('teacher-btn').classList.toggle('btn-outline-primary', userType !== 'teacher');
        }

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
    </script>
</body>
</html>