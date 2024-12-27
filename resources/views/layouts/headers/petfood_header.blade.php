@extends('layouts.headers.template_header')

@section('main-link')
<a href="{{ route('petfood_dashboard') }}" class="navbar-brand">
    <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
        class="brand-image" style="">
    <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
</a>
@endsection


@section('navlist')
<!-- Left navbar links -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a href="{{ route('petfood_dashboard') }}" class="nav-link">Dashboard</a>
    </li>
    <li class="nav-item">
        <a id="dropdownSubMenu1"  href="{{ route('issue_idt') }}"aria-haspopup="true" aria-expanded="false" class="nav-link">Issue IDT </a>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Receive IDT</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('list_receive', ['from_location' => '1570', 'to_location' => '3035']) }}" class="dropdown-item">Butchery -IDTs</a>
            </li>
            <hr class="dropdown-divider"/>
            <li><a href="{{ route('list_receive', ['from_location' => '2595', 'to_location' => '3035']) }}" class="dropdown-item">Highcare -IDTs</a>
            </li>
            <hr class="dropdown-divider" />
            <li><a href="{{ route('list_receive', ['from_location' => '2055', 'to_location' => '3035']) }}" class="dropdown-item">Sausage -IDTs</a>
            </li>
            <hr class="dropdown-divider" />
            <li><a href="{{ route('list_receive', ['from_location' => '3535', 'to_location' => '3035']) }}" class="dropdown-item">Despatch -IDTs</a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Scale Settings</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('scale_settings', 'petfood') }}" class="dropdown-item">
                   Scale Settings - Pet Food
                </a>
            </li>
        </ul>
    </li>
</ul>
@endsection
