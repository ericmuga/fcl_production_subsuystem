@extends('layouts.headers.template_header')

@section('main-link')
<a href="{{ route('butchery_dashboard') }}" class="navbar-brand">
    <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
        class="brand-image" style="">
    <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
</a>
@endsection

@section('navlist')
<!-- Left navbar links -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a href="{{ route('butchery_dashboard') }}" class="nav-link">Dashboard</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('butchery_dashboardv2') }}" class="nav-link">Dashboard V2</a>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Weigh</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('butchery_scale1_2') }}" class="dropdown-item">Scale 1-2
                    Beheading & breaking </a></li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('butchery_scale3') }}" class="dropdown-item">Scale 3
                    Deboning </a></li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('weigh_marination') }}" class="dropdown-item">Scale 4
                    Marination </a></li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Data Management</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('butchery_products') }}" class="dropdown-item"> Products</a></li>
            <hr class="dropdown-divider" />
            <li><a href="{{ route('list_items') }}" class="dropdown-item">Items</a></li>
            <hr class="dropdown-divider" />
            <li>
                <a href="{{ route('butchery_split_weights') }}" class="dropdown-item">Weight Splitting
                </a>
            </li>
            <li class="dropdown-divider"></li>

            <!-- Level two dropdown-->
            <li class="dropdown-submenu dropdown-hover">
                <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" class="dropdown-item dropdown-toggle">Reports</a>
                <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                    <li>
                        <a tabindex="-1" href="{{ route('butchery_beheading_report') }}" class="dropdown-item"> Beheading Report</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a tabindex="-1" href="{{ route('butchery_breaking_report') }}" class="dropdown-item"> Breaking Report</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a tabindex="-1" href="{{ route('butchery_deboning_report') }}" class="dropdown-item"> Deboning Report</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a tabindex="-1" href="{{ route('butchery_sales_report') }}" class="dropdown-item"> Sales Report</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a tabindex="-1" href="{{ route('butchery_transfers_report') }}" class="dropdown-item"> Transfers Report</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a tabindex="-1" href="{{ route('butchery_scale3_list') }}" class="dropdown-item"> Scale3 Products</a>
                    </li>
                </ul>
            </li>
            <!-- End Level two -->
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Receive IDT</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('list_receive', ['from_location' => '3535', 'to_location' => '1570']) }}" class="dropdown-item">Despatch - IDT</a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Settings</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('butchery_scale_settings', 'butchery') }}"
                    class="dropdown-item">Scale
                    settings </a></li>
        </ul>
    </li>
</ul>
@endsection
