<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container-fluid">
        <a href="#" class="navbar-brand">
            <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
                class="brand-image" style="">
            <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
        </a>

        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">

        <!-- Right navbar links -->
        @include('layouts.partials.right_nav')
    </div>
</nav>

<!-- logout modal -->
@include('layouts.partials.logout')
<!-- end logout -->
