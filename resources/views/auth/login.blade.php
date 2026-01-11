<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DjavaCluster Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
            background-color: #fff;
        }
        
        /* Layout Kiri (Gambar) */
        .login-image {
            background: url('https://images.unsplash.com/photo-1600596542815-2a4d9ffa9240?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
            background-size: cover;
            position: relative;
            height: 100vh;
        }
        
        /* Overlay Gelap di Gambar agar teks terbaca */
        .overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(30, 60, 114, 0.8) 0%, rgba(42, 82, 152, 0.9) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: white;
        }

        /* Layout Kanan (Form) */
        .login-form-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-wrap {
            width: 100%;
            max-width: 400px;
        }

        .form-control {
            height: 50px;
            border-radius: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding-left: 20px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
        }

        .btn-login {
            height: 50px;
            border-radius: 10px;
            background: #4e73df;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.4);
            transition: transform 0.2s;
        }

        .btn-login:hover {
            background: #2e59d9;
            transform: translateY(-2px);
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: #4e73df;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(78, 115, 223, 0.3);
        }

        /* Input Group Icon Styling */
        .input-group-text {
            background: transparent;
            border: none;
            position: absolute;
            right: 15px;
            top: 13px;
            z-index: 10;
            color: #aaa;
            cursor: pointer;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        /* Responsiveness */
        @media (max-width: 992px) {
            .login-image { display: none; }
        }
    </style>
</head>
<body>

    <div class="container-fluid p-0">
        <div class="row no-gutters">
            
            <div class="col-lg-7 d-none d-lg-block">
                <div class="login-image">
                    <div class="overlay">
                        <h1 class="font-weight-bold display-4">Smart Cluster<br>Management</h1>
                        <p class="lead mt-3 mb-5" style="opacity: 0.9;">
                            Kelola data warga, tagihan IPL, dan keamanan gate otomatis dalam satu dashboard terintegrasi.
                        </p>
                        <div class="small" style="opacity: 0.7;">
                            &copy; {{ date('Y') }} DjavaCluster System. All Rights Reserved.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="login-form-container">
                    <div class="login-wrap">
                        
                        <div class="brand-logo">
                            <i class="fas fa-dungeon"></i>
                        </div>

                        <h2 class="font-weight-bold text-gray-900 mb-1">Welcome Back!</h2>
                        <p class="text-muted mb-4">Silakan login untuk mengakses dashboard.</p>

                        @if ($errors->any())
                            <div class="alert alert-danger border-left-danger shadow-sm" role="alert">
                                <i class="fas fa-exclamation-circle mr-2"></i> {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route('login.post') }}" method="POST">
                            @csrf
                            
                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-gray-600 ml-1">ALAMAT EMAIL</label>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="email@example.com" value="{{ old('email') }}" required autofocus>
                            </div>

                            <div class="form-group mb-4">
                                <label class="small font-weight-bold text-gray-600 ml-1">PASSWORD</label>
                                <div class="input-wrapper">
                                    <input type="password" name="password" id="passwordInput" class="form-control" 
                                           placeholder="password" required>
                                    <span class="input-group-text" onclick="togglePassword()">
                                        <i class="far fa-eye" id="eyeIcon"></i>
                                    </span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-login">
                                Sign in
                            </button>

                            <!-- <div class="text-center mt-4">
                                <small class="text-muted">Lupa password? </small>
                            </div> -->
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function togglePassword() {
            var input = document.getElementById("passwordInput");
            var icon = document.getElementById("eyeIcon");
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>

</body>
</html>