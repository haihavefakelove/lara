<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>MyShop | Quản trị</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootswatch (Flatly) --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    {{-- Custom --}}
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <style>
        .navbar-brand { font-weight: 700; letter-spacing: .4px; }
        .page-title  { font-size: 1.35rem; font-weight: 700; }
        .table td, .table th { vertical-align: middle; }
        .btn-sm { padding: .22rem .55rem; }
        .badge-soft { background: #ecf7ff; color:#0d6efd; border:1px solid #b6e1ff; }
        .empty { padding: 4rem 0; color:#98a6ad; }
        .content-wrapper { padding-top: .75rem; padding-bottom: 2rem; }

        /* Hiện dropdown khi hover vào Dashboard trên desktop */
        @media (min-width: 992px) {
            .navbar .dropdown:hover .dropdown-menu {
                display: block;
                margin-top: 0; /* tránh giật */
            }
        }
        /* Làm nổi Dashboard khi đang ở trang con bất kỳ trong nhóm admin */
        .nav-link.active-parent {
            color: #fff;
            font-weight: 600;
        }
    </style>
</head>
@if(auth()->check() && auth()->user()->role === 'customer')
@include('partials.tawk')
@endif
</body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('shop.home') }}">MyShop</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#topNavbar" aria-controls="topNavbar" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div id="topNavbar" class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                {{-- ========== MENU ADMIN ========== --}}
                @php
                    $isAdmin      = auth()->check() && auth()->user()->role === 'admin';
                    $isProducts   = str_starts_with(request()->path(),'admin/products');
                    $isCategories = str_starts_with(request()->path(),'admin/categories');
                    $isOrders     = str_starts_with(request()->path(),'admin/orders');
                    $isReports    = str_starts_with(request()->path(),'admin/reports');
                    $isUsers      = str_starts_with(request()->path(),'admin/users');
                    $isCoupons    = str_starts_with(request()->path(),'admin/coupons');
                    $isDashboard  = request()->routeIs('admin.dashboard');
                    $isAnyAdminChild = $isProducts || $isCategories || $isOrders || $isReports || $isUsers || $isCoupons;
                @endphp

                @if($isAdmin)
                    {{-- Icon mở nhanh Dashboard --}}
                    <li class="nav-item">
                        <a class="nav-link px-2" href="{{ route('admin.dashboard') }}" title="Mở Dashboard">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </a>
                    </li>

                    {{-- Dropdown Dashboard --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isDashboard || $isAnyAdminChild ? 'active-parent' : '' }}"
                           href="{{ route('admin.dashboard') }}"
                           id="dashboardDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            Dashboard
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dashboardDropdown">
                            <li>
                                <a class="dropdown-item {{ $isProducts ? 'active' : '' }}"
                                   href="{{ route('admin.products.index') }}">
                                    <i class="bi bi-bag me-1"></i> Quản Lý Sản phẩm
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $isCategories ? 'active' : '' }}"
                                   href="{{ route('admin.categories.index') }}">
                                    <i class="bi bi-tags me-1"></i> Quản Lý Danh mục
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $isOrders ? 'active' : '' }}"
                                   href="{{ route('admin.orders.index') }}">
                                    <i class="bi bi-receipt me-1"></i> Quản Lý Đơn hàng
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $isReports ? 'active' : '' }}"
                                   href="{{ route('admin.reports.index') }}">
                                    <i class="bi bi-graph-up-arrow me-1"></i> Báo cáo Thống Kê
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $isUsers ? 'active' : '' }}"
                                   href="{{ route('admin.users.index') }}">
                                    <i class="bi bi-people me-1"></i> Quản Lý Người dùng
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $isCoupons ? 'active' : '' }}"
                                   href="{{ route('admin.coupons.index') }}">
                                    <i class="bi bi-ticket-perforated me-1"></i> Mã giảm giá
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item {{ request()->is('admin/pages*') ? 'active' : '' }}"
                                   href="{{ route('admin.pages.index') }}">
                                    <i class="bi bi-file-text me-1"></i> Trang tĩnh
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                {{-- ========== GIỎ HÀNG ========== --}}
                @if(auth()->check() && auth()->user()->role === 'customer')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('cart.index') ? 'active' : '' }}"
                       href="{{ route('cart.index') }}">
                        <i class="bi bi-cart4 me-1"></i>
                        Giỏ hàng
                        @php $count = array_sum(array_column(session('cart', []), 'quantity')); @endphp
                        @if($count > 0)
                            <span class="badge bg-warning text-dark ms-1">{{ $count }}</span>
                        @endif
                    </a>
                </li>
                @endif

                {{-- ========== ĐƠN HÀNG (CUSTOMER) ========== --}}
                @if(auth()->check() && auth()->user()->role === 'customer')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('orders.index') ? 'active' : '' }}"
                           href="{{ route('orders.index') }}">
                            <i class="bi bi-receipt-cutoff me-1"></i> Đơn hàng của tôi
                        </a>
                    </li>
                @endif
                
                {{-- ========== TRANG TĨNH (PUBLIC) ========== --}}
@unless($isAdmin)
    <li class="nav-item">
        <a class="nav-link {{ request()->is('page/ve-chung-toi') ? 'active' : '' }}"
           href="{{ route('page.show','ve-chung-toi') }}">Về chúng tôi</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->is('page/lien-he') ? 'active' : '' }}"
           href="{{ route('page.show','lien-he') }}">Liên hệ</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->is('page/chinh-sach-bao-mat') ? 'active' : '' }}"
           href="{{ route('page.show','chinh-sach-bao-mat') }}">Chính sách</a>
    </li>
@endunless

            </ul>
            {{-- ========== AUTH ========== --}}
            <ul class="navbar-nav ms-auto">
                @auth
                    <li class="nav-item d-flex align-items-center me-2 text-white-50">
                        Xin chào, <strong class="ms-1 text-white">{{ auth()->user()->name }}</strong>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-warning"
                           href="#"
                           onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">
                            <i class="bi bi-person-plus me-1"></i> Đăng ký
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<div class="container content-wrapper">
    @yield('content')
</div>
@if(auth()->check() && auth()->user()->role === 'customer')
@include('components.chat-popup')
@endif
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
