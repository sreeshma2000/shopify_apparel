<div class="header">
    <div class="menu-toggle-btn"> <!-- Menu close button for mobile devices -->
        <a href="#">
            <i class="bi bi-list"></i>
        </a>
    </div>
    <!-- Logo -->
    <a href="{{ url('/') }}" class="logo">
        <img width="100" src="{{ url('assets/images/logo.png') }}" alt="logo">
    </a>
    <!-- ./ Logo -->
    <div class="page-title">@yield('page-title')</div>
    <div class="search-form">
            <div class="input-group">
                <button class="btn btn-outline-light" type="button" id="button-addon1">
                    <i class="bi bi-search"></i>
                </button>
                <input type="text" class="form-control searchInput" placeholder="Search..." aria-label="" aria-describedby="button-addon1">
                <a href="#" class="btn btn-outline-light close-header-search-bar">
                    <i class="bi bi-x"></i>
                </a>
            </div>
    </div>
      
    <div class="header-bar ms-auto">
        <ul class="navbar-nav justify-content-end">
        </ul>
    </div>
    <!-- Header mobile buttons -->
    <div class="header-mobile-buttons">
        <a href="#" class="search-bar-btn">
            <i class="bi bi-search"></i>
        </a>
        <a href="#" class="actions-btn">
            <i class="bi bi-three-dots"></i>
        </a>
    </div>
    <!-- ./ Header mobile buttons -->
</div>

<div class="modal fade show" id="confirm-sync" tabindex="-1" aria-labelledby="confirm-sync-title"  aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirm-sync-title">Confirm Stock Refresh?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>This process will take time to refresh. Please wait.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close
                </button>
                <button type="button" class="btn btn-primary" id="confirm-sync-btn">Confirm</button>
            </div>
        </div>
    </div>
</div>