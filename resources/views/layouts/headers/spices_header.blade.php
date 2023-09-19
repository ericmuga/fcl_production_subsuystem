<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container-fluid">
        <a href="{{ route('spices_dashboard') }}" class="navbar-brand">
            <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
                class="brand-image" style="">
            <span class="brand-text font-weight-light"><strong> FCL Weight Management System | <small>Spices
                        Section</small></strong></span>
        </a>

        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="{{ route('spices_dashboard') }}" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Spices</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('batches_list', 'open') }}"
                                class="dropdown-item"> Open Batches
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('batches_list', 'closed') }}"
                                class="dropdown-item"> Closed Batches
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('batches_list', 'posted') }}"
                                class="dropdown-item"> Posted Batches
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Chopping</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('chopping_batches_list', 'open') }}"
                                class="dropdown-item"> Open Batches
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('chopping_batches_list', 'closed') }}"
                                class="dropdown-item"> Closed Batches
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('chopping_batches_list', 'posted') }}"
                                class="dropdown-item"> Posted Batches
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="nav-link dropdown-toggle">Data Management</a>
                    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                        <li><a href="{{ route('template_list') }}" class="dropdown-item"> Template
                                list
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('spices_items') }}" class="dropdown-item"> Items list
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('spices_stock') }}" class="dropdown-item"> Stocks list
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <li><a href="{{ route('spices_physical_stock') }}" class="dropdown-item">
                                Physical Stock
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        <!-- Level two dropdown-->
                        <li class="dropdown-submenu dropdown-hover">
                            <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" class="dropdown-item dropdown-toggle">Production</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a tabindex="-1" href="#" class="dropdown-item"> Spices posted Lines</a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <li>
                                    <a tabindex="-1" href="{{ route('chopping_posted_report') }}"
                                        class="dropdown-item"> Chopping posted Lines</a>
                                </li>
                                <li class="dropdown-divider"></li>
                                <!-- Level 3 dropdown-->
                                <li class="dropdown-submenu dropdown-hover">
                                    <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false"
                                        class="dropdown-item dropdown-toggle">Chopping Summary</a>
                                    <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                        <li>
                                            <a tabindex="-1"
                                                href="{{ route('chopping_posted_report_summary', 'today') }}"
                                                class="dropdown-item"> Today Lines Summary</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <!-- End Level two -->
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
