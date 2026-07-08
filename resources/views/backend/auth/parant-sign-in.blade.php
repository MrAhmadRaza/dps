<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>{{ config('app.name') }} | {{ $pageTitle }} </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{asset('backend_assets/logo/logo.png')}}" type="image/x-icon"/>
        <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="{{asset('backend_assets/css/bootstrap.min.css')}}" />
    <!--Font Awesome Icons-->
    <link rel="stylesheet" href="{{asset('backend_assets/font-awesome/css/all.min.css')}}" />
    <style>
        body.authentication-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* background: linear-gradient(135deg, #0d6efd, #6610f2); */
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
            /* padding: 0px; */
        }

        .auth-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
        }

        .auth-card .card-body {
            padding: 40px 30px;
        }

        .auth-logo h4 {
            font-weight: 700;
            color: #0d6efd;
        }

        .form-control {
            height: 45px;
            border-radius: 8px;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .btn-login {
            background-color: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-login:hover {
            background-color: #0b5ed7;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }

        .text-muted {
            font-size: 14px;
        }

        @media (max-width: 576px) {
            .auth-card .card-body {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body class="authentication-bg">

<div class="auth-wrapper">
    
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card auth-card">
        <div class="card-body">

            <div class="text-center auth-logo mb-4">
                <h4>DPS Parent Portal</h4>
            </div>

            <h3 class="text-center fw-bold">Sign In</h3>
            <p class="text-muted text-center mb-4">
                Enter your credentials to access dashboard
            </p>

            <form action="{{ route('parent.signIn.submit') }}" method="post">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Portal ID <span class="text-danger">*</span></label>
                    <input type="text" name="portal_id"
                        value="{{ old('portal_id') }}"
                        required
                        class="form-control"
                        placeholder="Portal ID">
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Password <span class="text-danger">*</span>
                    </label>
                
                    <div class="position-relative">
                        <input type="password"
                            name="password"
                            id="password"
                            required
                            class="form-control pe-5"
                            placeholder="Password">
                
                        <span class="position-absolute top-50 end-0 translate-middle-y me-3"
                            style="cursor:pointer;"
                            onclick="togglePassword()">
                            <i class="fa fa-eye" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                        class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <div class="d-grid">
                    <button class="btn btn-login py-2" type="submit">
                        Sign In
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    setTimeout(function () {
        $('.alert').fadeOut(1000, function () {
            $(this).remove();
        });
    }, 3000);
</script>

<script>
function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');

    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>