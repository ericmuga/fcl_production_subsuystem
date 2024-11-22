@extends('layouts.headers.template_header')

@section('main-link')
<a href="{{ route('slaughter_dashboard') }}" class="navbar-brand">
    <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
        class="brand-image" style="">
    <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
</a>
@endsection

@section('navlist')
<!-- Left navbar links -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a href="{{ route('slaughter_dashboard') }}" class="nav-link">Dashboard</a>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Lairage</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('slaughter_disease') }}" class="dropdown-item">Disease/Death</a></li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('lairage_transfers') }}" class="dropdown-item">Transfers</a></li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Weigh</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('slaughter_weigh') }}" class="dropdown-item">Carcass</a></li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('weigh_offals') }}" class="dropdown-item">Offals</a></li>
        </ul>
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
                    <hr class="dropdown-divider" />
                    <li>
                        <a tabindex="-1" href="{{ route('missing_slap_data') }}"
                            class="dropdown-item">Missing Slapmarks Report</a>
                    </li>
                    <hr class="dropdown-divider" />
                    <li>
                        <a tabindex="-1" href="{{ route('pending_etims') }}"
                            class="dropdown-item">Pending Etims Invoices</a>
                    </li>
                    <hr class="dropdown-divider" />
                    <li>
                        <a tabindex="-1" href="{{ route('lairage_transfer_reports') }}"
                            class="dropdown-item">Lairage Transfer Reports</a>
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
                <a href="{{ route('scale_settings', 'slaughter') }}"
                    class="dropdown-item">
                    Scale settings - Slaughter
                </a>
            </li>
            <hr class="dropdown-divider" />
            <li>
                <a href="{{ route('scale_settings', 'offals') }}"
                    class="dropdown-item">
                    Scale settings - Offals
                </a>
            </li>
        </ul>
    </li>
</ul>
@endsection
