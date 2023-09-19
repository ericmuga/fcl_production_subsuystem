<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container-fluid">
        <a href="{{ route('sausage_dashboard') }}" class="navbar-brand">
            <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo" class="brand-image" style="">
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
                    <a href="{{ route('despatch_dashboard') }}" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Receive IDT </a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('despatch_idt', 'sausage') }}" class="dropdown-item"> Sausage-IDT
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('despatch_idt', 'highcare') }}" class="dropdown-item">Highcare-IDT</a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('despatch_idt', 'highcare_bulk') }}" class="dropdown-item">HighcareBulk-IDT</a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('despatch_idt', 'fresh_cuts') }}" class="dropdown-item">Freshcuts-IDT</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Stocks</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('take_stocks') }}" class="dropdown-item">Stock Take
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Data Management</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="#" class="dropdown-item"> Today's Entries
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('despatch_idt_report', 'history') }}" class="dropdown-item"> IDT History
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Settings</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('butchery_scale_settings', 'despatch') }}" class="dropdown-item">Scale
                                settings 
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>

            <!-- SEARCH FORM -->
            <form class="form-inline ml-0 ml-md-3">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" type="search" placeholder="Search"
                        aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right navbar links -->
        @include('layouts.partials.right_nav')
    </div>
</nav>

<!-- logout modal -->
@include('layouts.partials.logout')
<!-- end logout -->
