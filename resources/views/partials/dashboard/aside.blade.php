{{-- Aside --}}

<div class="aside aside-left d-flex flex-column flex-row-auto" id="kt_aside">

    {{-- Brand --}}
    <div class="brand flex-column-auto" id="kt_brand">
        <div class="brand-logo">
            <a href="{{ url('/') }}">
                <img alt="{{ config('app.name') }}" src="https://www1.v12software.com/wp-content/uploads/LOGO_V12-1.png" width="160"/>
            </a>
        </div>

        <button class="brand-toggle btn btn-sm px-0" id="kt_aside_toggle">
            <img src="{{ asset('images/angle-double-left.svg') }}" width="10">
        </button>

    </div>

    {{-- Aside menu --}}
    @if(!isset($form))
    <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
        <div id="kt_aside_menu" class="aside-menu my-4" data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">
            <ul class="menu-nav">
                <li class="menu-item {{ Request::is('*/home') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                    <a href="{{ url('/dashboard/home') }}" class="menu-link">
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('*/customers') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                    <a href="{{ url('/customers') }}" class="menu-link">
                        <span class="menu-text">All Customers</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('*/all-performance') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                    <a href="{{ url('/all-performance') }}" class="menu-link">
                        <span class="menu-text">All Performance</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('*/weekly-performance') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                    <a href="{{ url('/weekly-performance') }}" class="menu-link">
                        <span class="menu-text">Weekly Performance</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('*/monthly-performance') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                    <a href="{{ url('/monthly-performance') }}" class="menu-link">
                        <span class="menu-text">Monthly Performance</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('*/all-active-customers') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                    <a href="{{ url('/all-active-customers') }}" class="menu-link">
                        <span class="menu-text">All Active Customers</span>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('*/customers-with-budget-and-no-campaign') ? 'menu-item-active' : '' }}" aria-haspopup="true">
                    <a href="{{ url('/customers-with-budget-and-no-campaign') }}" class="menu-link">
                        <span class="menu-text" style="color: #ff5733;">Customers With Budget And No Campaign</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    @endif
</div>