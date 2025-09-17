@extends('layouts.app')

@section('content')
<style>
  :root{
    --radius: 16px;
    --shadow-sm: 0 6px 20px rgba(0,0,0,.06);
    --shadow-md: 0 10px 30px rgba(0,0,0,.08);
    --primary: #0d6efd;
    --muted: #6b7280;
  }
  .page-title{ display:flex; align-items:center; gap:.6rem; margin-bottom:.25rem; }
  .subhead{ color:var(--muted); margin-bottom:1rem; }

  .toolbar { display:flex; gap:.5rem; align-items:center; margin-bottom:1rem; }
  .toolbar .btn{ border-radius:999px; }

  .card{ border:0; border-radius:var(--radius); box-shadow:var(--shadow-sm); }
  .card:hover{ box-shadow:var(--shadow-md); transition: box-shadow .25s ease; }
  .card-title{ font-weight:700; margin:0; display:flex; align-items:center; gap:.5rem; }

  .stat-card{
    background: linear-gradient(180deg, rgba(13,110,253,.08), #fff);
    border:0; border-radius: var(--radius);
  }
  .stat-label{ color:var(--muted); font-size:.95rem; }
  .stat-value{ font-size:1.75rem; font-weight:800; letter-spacing:.2px; }

  .table-wrap{ position:relative; border-radius: calc(var(--radius) - 6px); overflow:auto; }
  .table { margin:0; }
  .table thead th{
    position: sticky; top: 0; z-index: 2;
    background: #f8f9fa; border-bottom:1px solid #e9ecef;
  }
  .table tfoot th, .table tfoot td{
    background:#fafafa; font-weight:700; border-top:1px solid #e9ecef;
  }
  .table tbody tr:hover{ background: #f8fafc; }
  .text-money{ font-variant-numeric: tabular-nums; }
  .empty-cell{ color:var(--muted); font-style:italic; }
</style>

<h1 class="page-title">
  <i class="bi bi-graph-up-arrow text-primary"></i> Báo cáo
</h1>
<p class="subhead">Tổng quan hiệu suất đơn hàng, khách hàng và doanh thu theo thời gian / danh mục.</p>

<div class="toolbar">
  <a class="btn btn-outline-primary btn-sm {{ request()->routeIs('admin.reports.charts') ? 'active' : '' }}"
     href="{{ route('admin.reports.charts') }}">
    <i class="bi bi-bar-chart-line me-1"></i> Xem biểu đồ
  </a>
</div>

{{-- ===== Summary ===== --}}
<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-label">Tổng số đơn hàng</div>
        <div class="stat-value">{{ number_format($totalOrders) }}</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card h-100">
      <div class="card-body">
        <div class="stat-label">Tổng số khách hàng</div>
        <div class="stat-value">{{ number_format($totalCustomers) }}</div>
      </div>
    </div>
  </div>
</div>

@php
  $catSumQty   = collect($categoryRevenue ?? [])->sum(fn($x)=> (int)($x->total_qty ?? 0));
  $catSumRev   = collect($categoryRevenue ?? [])->sum(fn($x)=> (float)($x->total_revenue ?? 0));

  $dSumRev     = collect($revenueByDate ?? [])->sum(fn($x)=> (float)($x->total_revenue ?? 0));
  $dSumOrders  = collect($revenueByDate ?? [])->sum(fn($x)=> (int)($x->order_count ?? 0));

  $mSumRev     = collect($revenueByMonth ?? [])->sum(fn($x)=> (float)($x->total_revenue ?? 0));
  $mSumOrders  = collect($revenueByMonth ?? [])->sum(fn($x)=> (int)($x->order_count ?? 0));

  $ySumRev     = collect($revenueByYear ?? [])->sum(fn($x)=> (float)($x->total_revenue ?? 0));
  $ySumOrders  = collect($revenueByYear ?? [])->sum(fn($x)=> (int)($x->order_count ?? 0));
@endphp

{{-- ===== Category revenue ===== --}}
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h5 class="card-title"><i class="bi bi-grid-3x3-gap"></i> Doanh thu theo danh mục</h5>
  </div>
  <div class="table-wrap">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light">
      <tr>
        <th style="width:20%">Category ID</th>
        <th style="width:20%">Số lượng</th>
        <th>Doanh thu</th>
      </tr>
      </thead>
      <tbody>
      @forelse($categoryRevenue as $r)
        <tr>
          <td>{{ $r->category_id }}</td>
          <td>{{ number_format((int) $r->total_qty) }}</td>
          <td class="text-money">{{ number_format((float) $r->total_revenue, 0, ',', '.') }} đ</td>
        </tr>
      @empty
        <tr><td colspan="3" class="empty-cell">Không có dữ liệu.</td></tr>
      @endforelse
      </tbody>
      @if(($categoryRevenue ?? null) && count($categoryRevenue))
      <tfoot>
        <tr>
          <th>Tổng</th>
          <th>{{ number_format($catSumQty) }}</th>
          <th class="text-money">{{ number_format($catSumRev, 0, ',', '.') }} đ</th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

{{-- ===== Revenue by date ===== --}}
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h5 class="card-title"><i class="bi bi-calendar3"></i> Doanh thu theo ngày</h5>
  </div>
  <div class="table-wrap">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light">
      <tr>
        <th style="width:24%">Ngày</th>
        <th>Doanh thu</th>
        <th style="width:18%">Số đơn</th>
      </tr>
      </thead>
      <tbody>
      @forelse($revenueByDate as $r)
        <tr>
          <td>{{ \Carbon\Carbon::parse($r->date)->format('d/m/Y') }}</td>
          <td class="text-money">{{ number_format((float) $r->total_revenue, 0, ',', '.') }} đ</td>
          <td>{{ number_format((int) $r->order_count) }}</td>
        </tr>
      @empty
        <tr><td colspan="3" class="empty-cell">Không có dữ liệu.</td></tr>
      @endforelse
      </tbody>
      @if(($revenueByDate ?? null) && count($revenueByDate))
      <tfoot>
        <tr>
          <th>Tổng</th>
          <th class="text-money">{{ number_format($dSumRev, 0, ',', '.') }} đ</th>
          <th>{{ number_format($dSumOrders) }}</th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

{{-- ===== Revenue by month ===== --}}
<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h5 class="card-title"><i class="bi bi-calendar4-week"></i> Doanh thu theo tháng</h5>
  </div>
  <div class="table-wrap">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light">
      <tr>
        <th style="width:24%">Tháng</th>
        <th>Doanh thu</th>
        <th style="width:18%">Số đơn</th>
      </tr>
      </thead>
      <tbody>
      @forelse($revenueByMonth as $r)
        <tr>
          <td>{{ $r->month }}</td>
          <td class="text-money">{{ number_format((float) $r->total_revenue, 0, ',', '.') }} đ</td>
          <td>{{ number_format((int) $r->order_count) }}</td>
        </tr>
      @empty
        <tr><td colspan="3" class="empty-cell">Không có dữ liệu.</td></tr>
      @endforelse
      </tbody>
      @if(($revenueByMonth ?? null) && count($revenueByMonth))
      <tfoot>
        <tr>
          <th>Tổng</th>
          <th class="text-money">{{ number_format($mSumRev, 0, ',', '.') }} đ</th>
          <th>{{ number_format($mSumOrders) }}</th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>

{{-- ===== Revenue by year ===== --}}
<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="card-title"><i class="bi bi-calendar3-event"></i> Doanh thu theo năm</h5>
  </div>
  <div class="table-wrap">
    <table class="table table-sm table-hover align-middle">
      <thead class="table-light">
      <tr>
        <th style="width:24%">Năm</th>
        <th>Doanh thu</th>
        <th style="width:18%">Số đơn</th>
      </tr>
      </thead>
      <tbody>
      @forelse($revenueByYear as $r)
        <tr>
          <td>{{ $r->year }}</td>
          <td class="text-money">{{ number_format((float) $r->total_revenue, 0, ',', '.') }} đ</td>
          <td>{{ number_format((int) $r->order_count) }}</td>
        </tr>
      @empty
        <tr><td colspan="3" class="empty-cell">Không có dữ liệu.</td></tr>
      @endforelse
      </tbody>
      @if(($revenueByYear ?? null) && count($revenueByYear))
      <tfoot>
        <tr>
          <th>Tổng</th>
          <th class="text-money">{{ number_format($ySumRev, 0, ',', '.') }} đ</th>
          <th>{{ number_format($ySumOrders) }}</th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
</div>
@endsection
