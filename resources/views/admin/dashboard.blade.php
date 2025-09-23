@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="page-title mb-0">
            <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
        </h1>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-x-circle me-1"></i> {{ session('error') }}
            <button class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
        </div>
    @endif

    <p class="text-muted">Xin chào, <strong>{{ auth()->user()->name }}</strong>.</p>

    {{-- Quick stats (nếu controller có truyền vào, sẽ hiển thị; nếu không có, vẫn chạy bình thường) --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-bag fs-3 me-2 text-primary"></i>
                        <div>
                            <div class="fw-semibold">Sản phẩm</div>
                            <div class="text-muted">{{ $stats['products'] ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-tags fs-3 me-2 text-success"></i>
                        <div>
                            <div class="fw-semibold">Danh mục</div>
                            <div class="text-muted">{{ $stats['categories'] ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-receipt fs-3 me-2 text-warning"></i>
                        <div>
                            <div class="fw-semibold">Đơn hàng</div>
                            <div class="text-muted">{{ $stats['orders'] ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-people fs-3 me-2 text-info"></i>
                        <div>
                            <div class="fw-semibold">Người dùng</div>
                            <div class="text-muted">{{ $stats['users'] ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Các khối quản lý --}}
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex">
                    <i class="bi bi-bag fs-2 me-3 text-primary"></i>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Quản lý Sản phẩm</h5>
                        <p class="text-muted mb-3">Thêm mới, chỉnh sửa, quản lý kho & giá.</p>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-primary btn-sm">
                            Mở danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex">
                    <i class="bi bi-tags fs-2 me-3 text-success"></i>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Quản lý Danh mục</h5>
                        <p class="text-muted mb-3">Sắp xếp và phân loại sản phẩm.</p>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-success btn-sm">
                            Mở danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex">
                    <i class="bi bi-receipt fs-2 me-3 text-warning"></i>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Quản lý Đơn hàng</h5>
                        <p class="text-muted mb-3">Xử lý đơn, trạng thái & thanh toán.</p>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-warning btn-sm text-dark">
                            Mở danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex">
                    <i class="bi bi-graph-up-arrow fs-2 me-3 text-danger"></i>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Báo cáo Thống kê</h5>
                        <p class="text-muted mb-3">Doanh thu, đơn hàng, sản phẩm bán chạy.</p>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-danger btn-sm">
                            Xem báo cáo
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex">
                    <i class="bi bi-people fs-2 me-3 text-info"></i>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Quản lý Người dùng</h5>
                        <p class="text-muted mb-3">Tài khoản, phân quyền & hoạt động.</p>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-info btn-sm text-white">
                            Mở danh sách
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mã giảm giá (nếu bạn đã thêm module coupons) --}}
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body d-flex">
                    <i class="bi bi-ticket-detailed fs-2 me-3 text-secondary"></i>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Mã giảm giá</h5>
                        <p class="text-muted mb-3">Tạo & quản lý voucher khuyến mại.</p>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary btn-sm">
                            Quản lý mã
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
