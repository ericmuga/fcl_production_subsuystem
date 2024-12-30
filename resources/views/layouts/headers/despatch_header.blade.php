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
            <li><a href="{{ route('despatch_idt', 'highcare_bulk') }}" class="dropdown-item">HighcareBulk-IDT</a>
            </li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('despatch_idt', 'fresh_cuts') }}" class="dropdown-item">Freshcuts-IDT</a>
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
                <a href="{{ route('despatch_issue_idt', 'sausage') }}" class="dropdown-item"> To Sausage</a>
            </li>
            <hr class="dropdown-divider" />
            <li>
                <a href="{{ route('despatch_issue_idt', 'highcare') }}" class="dropdown-item">To High Care</a>
            </li>
            <hr class="dropdown-divider" />
            <li>
                <a href="{{ route('despatch_issue_idt', 'petfood') }}" class="dropdown-item">To Pet Food</a>
            </li>
            <hr class="dropdown-divider" />
            <li>
                <a href="{{  route('list_issued_idt', ['from_location' => '3535', 'to_location' => '4450']) }}" class="dropdown-item">To QA</a>
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
