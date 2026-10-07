<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ env('APP_NAME') }} | Log in</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets') }}/fontawesome-free/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets') }}/bootstrap/css/bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets') }}/dist/css/adminlte.min.css">

    <style>
        /* Fullscreen Background */
        .carousel-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            overflow: hidden;
            z-index: -1;
        }

        .carousel-inner {
            height: 100vh;
        }

        .carousel-item {
            height: 100vh;
        }

        .carousel-item img {
            width: 100%;
            height: 100vh;
            object-fit: cover;
        }

        /* Center Login Box */
        .login-box {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.85);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="hold-transition login-page">
    <!-- Background Image Carousel -->
    <div id="backgroundCarousel" class="carousel slide carousel-fade carousel-container" data-ride="carousel">
        <div class="carousel-inner">
            @php
                $imagePaths = [
                    'assets/images/img-1.png',
                    'assets/images/img-2.png',
                    'assets/images/img-3.png',
                    'assets/images/img-4.png',
                    'assets/images/img-5.png',
                    'assets/images/img-6.png',
                    'assets/images/img-7.png',
                    'assets/images/img-8.png',
                    'assets/images/img-9.png',
                ];
            @endphp

            @foreach ($imagePaths as $index => $image)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <img src="{{ asset($image) }}" class="d-block w-100" alt="Background Image {{ $index + 1 }}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="login-box">
        <div class="login-logo">
            <!-- <a href="#"><b>{{ env('APP_NAME') }}</b></a> -->
            
        </div>
        <!-- /.login-logo -->
        <div class="card">
        <img src="{{ asset('assets') }}/Picture1.svg" alt="AdminLTE Logo"
        class="brand-image" style="opacity: .8;max-height:100%;margin-left:0px;margin-right:0px;float:none;">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                <form action="{{ route('login.store') }}" method="post" novalidate>
                    @csrf
                    <div class="input-group mb-3">
                        <input type="text" class="form-control @error('email_or_username') is-invalid @enderror"
                            value="{{ old('email_or_username') }}" name="email_or_username"
                            placeholder="Enter Email or Mobile" autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('email_or_username')" class="mt-2" />
                    <div class="input-group mb-3">
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter Your password" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    <div class="row">
                        <!-- /.col -->
                        <div class="col-4">
                            <button type="submit" class="btn btn-danger btn-block">Sign In</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="{{ asset('assets') }}/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets') }}/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('assets') }}/js/adminlte.min.js"></script>

    <script>
        // Activate Carousel
        $('#backgroundCarousel').carousel({
            interval: 5000, // Change image every 5 seconds
            pause: false
        });
    </script>

</body>

</html>
