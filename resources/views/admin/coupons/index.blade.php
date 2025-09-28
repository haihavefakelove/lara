@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-ticket-perforated me-2"></i>Mã giảm giá</h1>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary"><i class="bi bi-plus"></i> Tạo mã</a>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }} <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Mã</th>
                <th>Loại</th>
                <th>Giá trị</th>
                <th>Lượt dùng</th>
                <th>Giới hạn</th>
                <th>Đơn tối thiểu</th>
                <th>Đơn tối đa</th> 
                <th>Kích hoạt</th>
                <th>Thời gian</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($coupons as $i => $c)
            <tr>
                <td>{{ $coupons->firstItem() + $i }}</td>
                <td class="fw-semibold">{{ $c->code }}</td>
                <td>{{ strtoupper($c->type) }}</td>
                <td>{{ $c->type=='percent' ? $c->value.' %' : number_format($c->value,0,',','.') . ' đ' }}</td>
                <td>{{ $c->used }}</td>
                <td>{{ $c->max_uses ?? '∞' }}</td>
                <td>{{ $c->min_order ? number_format($c->min_order,0,',','.') . ' đ' : '-' }}</td>
                <td>{{ $c->max_order ? number_format($c->max_order,0,',','.') . ' đ' : '-' }}</td> {{-- hiển thị max_order --}}
                <td>{!! $c->is_active ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>' !!}</td>
                <td>
                    @if($c->start_at) {{ $c->start_at->format('d/m/Y') }} @endif -
                    @if($c->end_at)   {{ $c->end_at->format('d/m/Y') }}   @endif
                </td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.coupons.edit',$c) }}"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('admin.coupons.destroy',$c) }}" class="d-inline" method="POST" onsubmit="return confirm('Xoá?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $coupons->links() }}
    </div>
</div>
@endsection
