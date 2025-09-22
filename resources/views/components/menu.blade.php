<div class="menu">
    <div class="menu-header">
        <a href="{{ url('/admin') }}" class="menu-header-logo">
            <h2 style="margin: 0; padding: 10px 0; font-weight: bold;"></h2>
            {{-- @if($isSimulationMode)
            <p style="font-size: 11px;
            padding-left: 15px;
            padding-top: 20px;
            color: #faae42;">Simulation<br>Mode</p>
            @endif --}}
        </a>
        <a href="{{ url('/') }}" class="btn btn-sm menu-close-btn">
            <i class="bi bi-x"></i>
        </a>
    </div>
    <div class="menu-body">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center" data-bs-toggle="dropdown">
                <div class="avatar me-3">
                    <div class="avatar avatar-primary me-1">
                        <span class="avatar-text rounded-circle">{{strtoupper(substr(auth()->user()->name, 0, 1))}}</span>
                    </div>
                </div>
                <div style="width: 90%;">
                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                    <small class="text-muted">{{ auth()->user()->email }}</small>
                </div>
                <div class="">
                    <i class="bi bi-gear"></i>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <a href="{{ route('account.index') }}" class="dropdown-item d-flex align-items-center">
                    <i class="bi bi-person dropdown-item-icon"></i> Profile
                </a>
                <a href="javascript:;" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="dropdown-item d-flex align-items-center text-danger">
                    <i class="bi bi-box-arrow-right dropdown-item-icon"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>
        <ul>
            <li>
                <a href="{{route('dashboard')}}" @if (request()->routeIs('dashboard')) class="active" @endif>
                    <span class="nav-link-icon">
                        <i class="bi bi-bar-chart"></i>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('product.index') }}" @if (request()->routeIs('product.index')) class="active" @endif>
                    <span class="nav-link-icon">
                        <i class="bi bi-bag"></i>
                    </span>
                    <span>Products</span>
                </a>
            </li>
            <li>
                <a href="{{ route('order.index') }}" @if (request()->routeIs('order.index')) class="active" @endif>
                    <span class="nav-link-icon">
                      <i class="bi bi-cart"></i>
                    </span>
                    <span>Orders</span>
                </a>
            </li>
            <li>
                <a href="{{ route('report.index') }}" @if (request()->routeIs('report.index')) class="active" @endif>
                    <span class="nav-link-icon">
                      <i class="bi bi-cart"></i>
                    </span>
                    <span>Reports</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="nav-link-icon">
                    <i class="fa fa-cog" aria-hidden="true"></i>
                    </span>
                    <span>Settings</span>
                </a>
                <ul>
                    <li><a href="{{ route('setting.index', ['platform' => 'shopify']) }}"
                        @if (request()->fullUrl() == route('setting.index', ['platform' => 'shopify'])) class="active" @endif>shopify</a></li>
                    <li><a href="{{ route('setting.index', ['platform' => 'apparelmagic']) }}"
                        @if (request()->fullUrl() == route('setting.index', ['platform' => 'apparelmagic'])) class="active" @endif>apparel magic</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>