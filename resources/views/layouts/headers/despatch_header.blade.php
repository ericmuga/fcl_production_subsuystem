@extends('layouts.headers.template_header')

@section('main-link')
<a href="{{ route('despatch_dashboard') }}" class="navbar-brand">
    <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo" class="brand-image" style="">
    <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
</a>
@endsection

@section('navlist')
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
            <li><a href="{{ route('despatch_idt', 'curing') }}" class="dropdown-item">Curing-IDT</a>
            </li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('despatch_idt', 'highcare_bulk') }}" class="dropdown-item">HighcareBulk-IDT</a>
            </li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('despatch_idt', 'fresh_cuts') }}" class="dropdown-item">Freshcuts-IDT</a>
            </li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('despatch_idt', 'petfood') }}" class="dropdown-item">Petfood-IDT</a>
            </li>
            <li class="dropdown-divider"></li>
            <li class="dropdown-submenu">
                <a id="dropdownSubMenu2" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="dropdown-item dropdown-toggle">Despatch IDT</a>
                <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                    <li><a href="{{ route('despatch_idt', 'export') }}" class="dropdown-item">Export-IDT</a></li>
                    <li class="dropdown-divider"></li>
                    <li><a href="{{ route('despatch_idt', 'local') }}" class="dropdown-item">Local 3535-IDT</a></li>
                    <li class="dropdown-divider"></li>
                    <li><a href="{{ route('despatch_idt', 'old_factory') }}" class="dropdown-item">Old Factory-IDT</a></li>
                </ul>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Issue IDT</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('despatch_issue_idt', 'butchery') }}" class="dropdown-item"> To Butchery</a>
            </li>
            <hr class="dropdown-divider" />
            <li>
                <a href="{{ route('despatch_issue_idt') }}" class="dropdown-item">Finished Goods</a>
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
@endsection
