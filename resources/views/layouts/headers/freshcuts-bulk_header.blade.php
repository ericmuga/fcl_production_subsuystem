@extends('layouts.headers.template_header')

@section('main-link')
<a href="{{ route('freshcuts_bulk_dashboard') }}" class="navbar-brand">
    <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
        class="brand-image" style="">
    <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
</a>
@endsection

@section('navlist')
<!-- Left navbar links -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a href="{{ route('freshcuts_bulk_dashboard') }}" class="nav-link">Dashboard</a>
    </li>
    <li class="nav-item">
        <a href="{{ route('freshcuts_bulk_idt') }}" class="nav-link">IDT</a>
    </li>
    @if(auth()->user()->role == 'kitchen')
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu2" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Receive IDT</a>
        <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">                        
            <li>
                <a href="{{ route('list_receive', ['from_location' => '1570', 'to_location' => '4400']) }}" class="dropdown-item">Kitchen -IDTs</a>
            </li>
        </ul>
    </li>
    @endif
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Data Management</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">                        
            <li><a href="{{ route('freshcuts_bulk_report') }}" class="dropdown-item"> IDT History
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Settings</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('butchery_scale_settings', 'fresh_bulk') }}"
                    class="dropdown-item">Scale
                    settings 
                </a>
            </li>
        </ul>
    </li>
</ul>
@endsection
