@extends('layouts.headers.template_header')

@section('main-link')
<a href="{{ route('sausage_dashboard') }}" class="navbar-brand">
    <img src="{{ asset('assets/img/fcl1.png') }}" alt="FCL Calibra Logo"
        class="brand-image" style="">
    <span class="brand-text font-weight-light"><strong> FCL Weight Management System</strong></span>
</a>
@endsection

@section('navlist')
<!-- Left navbar links -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a href="{{ route('sausage_dashboard') }}" class="nav-link">Dashboard</a>
    </li>
    <li class="nav-item">
        <a id="dropdownSubMenu1"  href="{{ route('sausage_idt') }}"aria-haspopup="true" aria-expanded="false" class="nav-link">Issue IDT </a>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Receive IDT</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('stuffing_weights') }}" class="dropdown-item">Stuffing IDTs</a></li>
            <hr class="dropdown-divider"/>
            <li><a href="{{ route('sausage_idt_receive') }}" class="dropdown-item">Butchery IDTs</a>
            </li>
            <hr class="dropdown-divider" />
            <li><a href="{{ route('list_receive', ['from_location' => '2595', 'to_location' => '2055']) }}" class="dropdown-item">HighCare -IDTs</a>
            </li>
            <hr class="dropdown-divider" />
            <li><a href="{{ route('list_receive', ['from_location' => '3535', 'to_location' => '2055']) }}" class="dropdown-item">Despatch -IDTs</a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Data Management</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li><a href="{{ route('sausage_entries') }}" class="dropdown-item"> Today's
                    Entries
                </a>
            </li>
            <li class="dropdown-divider"></li>
            <li><a href="{{ route('items_list') }}" class="dropdown-item"> Items List
                </a>
            </li>
            <li class="dropdown-divider"></li>                        
            <!-- Level two dropdown-->
            <li class="dropdown-submenu dropdown-hover">
                <a id="dropdownSubMenu2" href="#" role="button" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" class="dropdown-item dropdown-toggle">Reports</a>
                <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                    <li>
                        <a tabindex="-1" href="{{ route('sausage_idt_report', 'history') }}"
                            class="dropdown-item"> IDT History</a>
                    </li>
                    <li class="dropdown-divider"></li>
                    <li>
                        <a tabindex="-1" href="{{ route('per_batch_sausage') }}"
                            class="dropdown-item"> Per Batch Today</a>
                    </li>                                
                </ul>
            </li>
            <!-- End Level two -->
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle">Scale Settings</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('scale_settings', 'stuffing') }}" class="dropdown-item">
                   Scale Settings - Stuffing Weights
                </a>
            </li>
        </ul>
    </li>
</ul>
@endsection
