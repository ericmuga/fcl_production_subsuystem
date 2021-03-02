<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
        <a href="{{ route('slaughter_dashboard') }}" class="navbar-brand">
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
                    <a href="{{ route('slaughter_dashboard') }}" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('slaughter_weigh') }}" class="nav-link">Weigh</a>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Data Management</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('slaughter_receipts') }}" class="dropdown-item">Imported
                                Receipts</a></li>

                        <li class="dropdown-divider"></li>

                        <!-- Level two dropdown-->
                        <li class="dropdown-submenu dropdown-hover">
                            <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" class="dropdown-item dropdown-toggle">Reports</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a tabindex="-1" href="{{ route('slaughter_data_report') }}"
                                        class="dropdown-item">Slaughter Report</a>
                                </li>
                                <li>
                                    <a tabindex="-1" href="{{ route('missing_slap_data') }}"
                                        class="dropdown-item">Missing Slapmarks Report</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End Level two -->
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Settings</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('slaughter_scale_settings') }}"
                                class="dropdown-item">Scale
                                settings </a></li>
                        <li class="dropdown-divider"></li>
                        {{-- <li><a href="{{ route('slaughter_change_password') }}"
                                class="dropdown-item">Change password</a></li> --}}
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
        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" style="color:gray" href="#" id="userDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user-circle"></i> {{ Session::get('session_userName') }}
                </a>
                <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                    <li>
                        <a href="{{ route('redirect_page') }}" class="dropdown-item"><i class="fas fa-exchange-alt"></i> Switch
                            Interphase
                        </a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal"><i
                                class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

<!-- logout modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Please confirm if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-flat " type="button" data-dismiss="modal">Cancel</button>
                <a href="{{ route('logout') }}" type="submit"
                    class="btn btn-warning btn-lg  float-right"><i class="fa fa-send"></i> Logout</a>
            </div>
        </div>
    </div>
</div>
<!-- end logout -->
