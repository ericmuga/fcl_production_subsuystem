@extends('layouts.headers.template_header')

@section('main-link')
<a href="{{ route('highcare1_dashboard') }}" class="navbar-brand">
    <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
        class="brand-image" style="">
    <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
</a>
@endsection

@section('navlist')
<!-- Left navbar links -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a href="{{ route('highcare1_dashboard') }}" class="nav-link">Dashboard</a>
    </li>
    <!-- <li class="nav-item">
        <a href="{{ route('highcare1_idt') }}" aria-haspopup="true" aria-expanded="false" class="nav-link">Create IDT </a>
    </li> -->
    <li class="nav-item dropdown">
        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Create IDT </a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('highcare1_idt') }}" class="dropdown-item">IDT To HighCare</a>
            </li>
            <hr class="dropdown-divider" />
            <li>
                <a href="{{ route('highcare1_idt', '2500') }}" class="dropdown-item"> IDT To Curing</a>
            </li>
        </ul>
    </li>    
    <li class="nav-item dropdown">
        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Receive IDT </a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('highcare_idt_receive') }}" class="dropdown-item">Butchery IDT</a>
            </li>
            <hr class="dropdown-divider" />
            <li>
                <a href="{{ route('list_receive', ['from_location' => '3535', 'to_location' => '2595']) }}" class="dropdown-item">Despatch IDT</a>
            </li>
        </ul>
    </li>    
    <li class="nav-item">
        <a href="{{ route('highcare1_idt_bulk') }}" class="nav-link">Bulk-IDT</a>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle"> Production </a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">                        
            <li><a href="{{ route('bacon_slicing') }}"
                    class="dropdown-item">Bacon Slicing</a>
            </li>            
        </ul>
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
@endsection
