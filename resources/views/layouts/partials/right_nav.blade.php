<ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
    <li class="nav-item dropdown no-arrow">
        <a class="nav-link dropdown-toggle" style="color:black" href="#" id="userDropdown" role="button"
            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-user-circle"></i> {{ Session::get('session_userName') }}
        </a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('redirect_page') }}" class="dropdown-item"><i
                        class="fas fa-exchange-alt"></i> Switch
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
