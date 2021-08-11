<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="Ephantus Karanja">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="shortcut icon" style="" href="{{ asset('assets/img/fcl1.png') }}">
    <title>WMS | {{ $title }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/summernote/summernote-bs4.min.css') }}">
    <!-- toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">

        <!-- Navbar -->
        @include('layouts.headers.router_header')
        <!-- /.navbar -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                @yield('content-header')
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="row">
                    <div class="container-fluid">
                        <div class="card-deck-wrapper">
                            <div class="card-deck">
                                <div class="card p-2 bg-info" style="height: 200px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none"
                                        href="{{ route('slaughter_dashboard') }}">
                                        <h4 class="card-title">Slaughter</h4>
                                        <p class="card-text">Select this option to switch to slaughter.
                                        </p>
                                    </a>
                                </div>
                                <div class="card p-2 bg-warning" style="height: 200px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none"
                                        href="{{ route('butchery_dashboard') }}">
                                        <h4 class="card-title">Butchery</h4>
                                        <p class="card-text">Select this option to switch to butchery.
                                        </p>
                                    </a>
                                </div>
                                <div class="card p-2 bg-success" style="height: 200px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                                        href="{{ route('stock_dashboard') }}">
                                        <h4 class="card-title">Butchery Stocks</h4>
                                        <p class="card-text">Select this option to switch to Butchery Stocks section.
                                        </p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <hr>
                <div class="row">
                    <div class="container-fluid">
                        <div class="card-deck-wrapper">
                            <div class="card-deck">
                                <div class="card p-2 bg-dark" style="height: 200px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none"
                                        href="{{ route('sausage_dashboard') }}">
                                        <h4 class="card-title">Sausage</h4>
                                        <p class="card-text">Select this option to switch to Sausage Section.
                                        </p>
                                    </a>
                                </div>
                                <div class="card p-2 bg-primary" style="height: 200px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                                        href="#">
                                        <h4 class="card-title">High Care</h4>
                                        <p class="card-text">Select this option to switch to High Care interface.
                                        </p>
                                    </a>
                                </div>
                                <div class="card p-2 bg-danger" style="height: 200px">
                                    <a class="card-body text-center card-block stretched-link text-decoration-none card-link"
                                        href="#">
                                        <h4 class="card-title">Dispatch</h4>
                                        <p class="card-text">Select this option to switch to Dispatch interface.
                                        </p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->

            <!-- Main Footer -->
            <div class="row">
                <div class="div" style="position: fixed; bottom: 0; width: 100%">
                    @include('layouts.footers.footer')
                </div>
            </div>
        </div>
        <!-- ./wrapper -->

        <!-- REQUIRED SCRIPTS -->

        <!-- jQuery -->
        <script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
        <!-- Bootstrap 4 -->
        <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}">
        </script>
        <!-- overlayScrollbars -->
        <script src="{{ asset('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}">
        </script>
        <!-- toastr -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
        {!! Toastr::message() !!}
        
        @yield('scripts')

</body>

</html>
