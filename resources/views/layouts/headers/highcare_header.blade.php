<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container-fluid">
        <a href="{{ route('highcare1_dashboard') }}" class="navbar-brand">
            <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
                class="brand-image" style="">
            <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
        </a>

        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="{{ route('highcare1_dashboard') }}" class="nav-link">Dashboard</a>
                </li>
                {{-- <li class="nav-item">
                    <a href="{{ route('highcare1_idt') }}" class="nav-link">IDT</a>
                </li> --}}
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle"> IDT </a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">                        
                        <li><a href="{{ route('highcare1_idt') }}"
                                class="dropdown-item">Create IDT</a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('highcare_idt_receive') }}"
                                class="dropdown-item">Receive From Butch-IDT</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('highcare1_idt_bulk') }}" class="nav-link">Bulk-IDT</a>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Data Management</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">                        
                        <li><a href="{{ route('highcare1_idt_report') }}" class="dropdown-item"> IDT History
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Settings</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('butchery_scale_settings', 'highcare1') }}"
                                class="dropdown-item">Scale
                                settings 
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <!-- SEARCH FORM -->
            @include('layouts.partials.nav_search_form')
        </div>

        <!-- Right navbar links -->
        @include('layouts.partials.right_nav')
    </div>
</nav>

<!-- logout modal -->
@include('layouts.partials.logout')
<!-- end logout -->
