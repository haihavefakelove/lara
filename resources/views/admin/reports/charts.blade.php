@extends('layouts.app')

@section('content')
<style>
  :root{
    --card-radius: 16px;
    --shadow-sm: 0 4px 18px rgba(0,0,0,.06);
    --shadow-md: 0 8px 30px rgba(0,0,0,.08);
    --primary: #0d6efd;
    --primary-50: rgba(13,110,253,.08);
    --muted: #6b7280;
  }

  .page-title{ display:flex; align-items:center; gap:.6rem; margin-bottom:1rem; }
  .page-title .lead{ color: var(--muted); }

  .stat-pill{
    border-radius: 999px;
    background: var(--primary-50);
    padding: .375rem .75rem;
    font-weight:600; color:#0b5ed7;
    display:inline-flex; align-items:center; gap:.4rem;
  }

  .card{ border:0; border-radius: var(--card-radius); box-shadow: var(--shadow-sm); }
  .card-header{
    border:0; background: transparent;
    font-weight:700; display:flex; align-items:center; justify-content:space-between;
  }
  .card-header .tools .btn{
    --bs-btn-padding-y: .15rem;
    --bs-btn-padding-x: .45rem;
    --bs-btn-font-size: .8rem;
  }

  .chart-wrap{ position:relative; min-height: 380px;}
  .chart-wrap canvas{ width:100% !important; height: 380px !important; }

  .chart-empty{
    position:absolute; inset:0; display:none; align-items:center; justify-content:center;
    color: var(--muted); backdrop-filter:saturate(140%) blur(1px);
  }
  .chart-empty.show{ display:flex; }

  .card:hover{ box-shadow: var(--shadow-md); transition: box-shadow .25s ease; }

  @media (max-width: 576px){
    .chart-wrap, .chart-wrap canvas{ min-height: 300px; height: 300px !important; }
  }
</style>

<h1 class="page-title">
  <i class="bi bi-bar-chart-line fs-3 text-primary"></i>
  <span>Biểu đồ báo cáo</span>
</h1>
<p class="lead mb-4">Góc nhìn tổng quan hiệu quả doanh thu theo danh mục, thời gian và phương thức thanh toán.</p>

{{-- Stat pills --}}
<div class="mb-4">
  <span class="stat-pill me-2"><i class="bi bi-calendar3"></i> <span id="pill-30d">—</span></span>
  <span class="stat-pill me-2"><i class="bi bi-calendar4-week"></i> <span id="pill-12m">—</span></span>
  <span class="stat-pill me-2"><i class="bi bi-columns-gap"></i> <span id="pill-cats">—</span></span>
  <span class="stat-pill"><i class="bi bi-piggy-bank"></i> <span id="pill-year">—</span></span>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <span>Doanh thu theo danh mục</span>
        <div class="tools">
          <button class="btn btn-outline-secondary btn-sm" data-dl="categoryRevenueChart"><i class="bi bi-download"></i> PNG</button>
        </div>
      </div>
      <div class="card-body chart-wrap">
        <canvas id="categoryRevenueChart" aria-label="Biểu đồ doanh thu theo danh mục"></canvas>
        <div class="chart-empty" id="empty-category">Không có dữ liệu</div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <span>Doanh thu theo ngày (30 ngày)</span>
        <div class="tools">
          <button class="btn btn-outline-secondary btn-sm" data-dl="revenueByDateChart"><i class="bi bi-download"></i> PNG</button>
        </div>
      </div>
      <div class="card-body chart-wrap">
        <canvas id="revenueByDateChart" aria-label="Biểu đồ doanh thu theo ngày"></canvas>
        <div class="chart-empty" id="empty-date">Không có dữ liệu</div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <span>Doanh thu theo tháng (12 tháng)</span>
        <div class="tools">
          <button class="btn btn-outline-secondary btn-sm" data-dl="revenueByMonthChart"><i class="bi bi-download"></i> PNG</button>
        </div>
      </div>
      <div class="card-body chart-wrap">
        <canvas id="revenueByMonthChart" aria-label="Biểu đồ doanh thu theo tháng"></canvas>
        <div class="chart-empty" id="empty-month">Không có dữ liệu</div>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <span>Doanh thu theo năm</span>
        <div class="tools">
          <button class="btn btn-outline-secondary btn-sm" data-dl="revenueByYearChart"><i class="bi bi-download"></i> PNG</button>
        </div>
      </div>
      <div class="card-body chart-wrap">
        <canvas id="revenueByYearChart" aria-label="Biểu đồ doanh thu theo năm"></canvas>
        <div class="chart-empty" id="empty-year">Không có dữ liệu</div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <span>Doanh thu theo phương thức thanh toán</span>
        <div class="tools">
          <button class="btn btn-outline-secondary btn-sm" data-dl="revenueByPaymentMethodChart"><i class="bi bi-download"></i> PNG</button>
        </div>
      </div>
      <div class="card-body chart-wrap">
        <canvas id="revenueByPaymentMethodChart" aria-label="Biểu đồ doanh thu theo phương thức thanh toán"></canvas>
        <div class="chart-empty" id="empty-pay">Không có dữ liệu</div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
  // ====== DỮ LIỆU TỪ CONTROLLER ======
  const catLabels       = @json($catLabels ?? []);
  const catRevenue      = (@json($catRevenue ?? []) || []).map(Number);

  const revDateLabels   = @json($revDateLabels ?? []);
  const revDateData     = (@json($revDateData ?? []) || []).map(Number);

  const revMonthLabels  = @json($revMonthLabels ?? []);
  const revMonthData    = (@json($revMonthData ?? []) || []).map(Number);

  const revYearLabels   = @json($revYearLabels ?? []);
  const revYearData     = (@json($revYearData ?? []) || []).map(Number);

  // Với phương thức thanh toán: controller có thể trả về keys thô (cod|momo|payos)
  // hoặc nhãn đã format. Ta vẫn chuẩn hóa lại ở view để đảm bảo PayOS hiển thị đẹp.
  const payRawLabels = @json($paymentMethodLabels ?? []);         // ví dụ: ["cod","momo","payos"] hoặc ["COD","MoMo","PayOS (VietQR/MB)"]
  const payRevenue   = (@json($paymentMethodRevenue ?? []) || []).map(Number);

  // Chuẩn hóa nhãn phương thức thanh toán
  const labelMap = {
    'cod'   : 'COD',
    'momo'  : 'MoMo',
    'payos' : 'PayOS (VietQR/MB)'
  };
  const payLabels = (payRawLabels || []).map(l => {
    if (!l) return '';
    const k = l.toString().trim().toLowerCase();
    return labelMap[k] ?? l; // nếu đã format đẹp từ controller thì giữ nguyên
  });

  // ====== TIỆN ÍCH ======
  const fmtVND = (n)=> new Intl.NumberFormat('vi-VN', { style:'currency', currency:'VND', maximumFractionDigits:0 }).format(+n||0);
  const sum = (arr)=> (arr||[]).reduce((a,b)=> a + (+b||0), 0);
  const palette = ['#6366f1','#22c55e','#ef4444','#0ea5e9','#f59e0b','#84cc16','#a855f7','#06b6d4','#f97316','#10b981','#e11d48','#14b8a6'];
  const PRIMARY = '#0d6efd';

  // Ưu tiên màu cố định cho 3 phương thức phổ biến (để lần nào cũng “đúng màu”)
  const payColorMap = {
    'MoMo'               : '#6366f1', // tím indigo
    'COD'                : '#22c55e', // xanh lá
    'PayOS (VietQR/MB)'  : '#10b981'  // teal
  };

  const hexToRgba = (hex, alpha=0.3)=>{
    const v = hex.replace('#','');
    const bigint = parseInt(v.length===3 ? v.split('').map(c=>c+c).join('') : v, 16);
    const r = (bigint >> 16) & 255, g = (bigint >> 8) & 255, b = bigint & 255;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  };

  const makeGradient = (ctx, area, color=PRIMARY, alphaTop=0.25)=>{
    const g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
    g.addColorStop(0, hexToRgba(color, alphaTop));
    g.addColorStop(1, 'rgba(255,255,255,0)');
    return g;
  };

  // Plugin hiện chữ “Không có dữ liệu”
  const noDataPlugin = {
    id: 'noData',
    afterDraw(chart) {
      const any = (chart.data.datasets||[]).some(ds => (ds.data||[]).some(v => (+v||0) > 0));
      const idMap = {
        categoryRevenueChart: 'empty-category',
        revenueByDateChart:   'empty-date',
        revenueByMonthChart:  'empty-month',
        revenueByYearChart:   'empty-year',
        revenueByPaymentMethodChart: 'empty-pay'
      };
      const overlay = document.getElementById(idMap[chart.canvas.id]);
      if(!overlay) return;
      overlay.classList.toggle('show', !any);
      if(!any){
        const {ctx, chartArea} = chart;
        if(!chartArea) return;
        ctx.save();
        ctx.fillStyle = '#6b7280';
        ctx.font = '600 14px system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Không có dữ liệu', (chartArea.left+chartArea.right)/2, (chartArea.top+chartArea.bottom)/2);
        ctx.restore();
      }
    }
  };

  // Plugin in tổng ở giữa doughnut
  const centerTextPlugin = {
    id: 'centerText',
    beforeDraw(chart){
      if(chart.config.type !== 'doughnut') return;
      const total = sum(chart.data.datasets?.[0]?.data || []);
      const {width, height, ctx} = chart;
      ctx.save();
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillStyle = '#111827';
      ctx.font = '600 13px system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial';
      ctx.fillText('Tổng', width/2, height/2 - 12);
      ctx.font = '700 14px system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial';
      ctx.fillText(total ? fmtVND(total) : '0', width/2, height/2 + 10);
      ctx.restore();
    }
  };

  Chart.register(noDataPlugin, centerTextPlugin);

  // Tooltip chung
  const commonTooltip = {
    callbacks: {
      label: (ctx)=>{
        const label = ctx.dataset.label || '';
        const v = ctx.parsed?.y ?? ctx.parsed;
        if(ctx.chart.config.type === 'doughnut'){
          const total = sum(ctx.dataset.data || []);
          const pct = total ? (v/total*100) : 0;
          return `${ctx.label}: ${fmtVND(v)} (${pct.toFixed(1)}%)`;
        }
        return `${label}: ${fmtVND(v)}`;
      }
    }
  };

  // ====== TẠO CHART ======
  const mkCartesian = (canvasId, type, labels, data, color=PRIMARY) => {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
      type,
      data: {
        labels,
        datasets: [{
          label: 'Doanh thu (VNĐ)',
          data,
          borderColor: color,
          borderWidth: 2,
          pointRadius: 2,
          pointHoverRadius: 5,
          fill: true,
          backgroundColor: (context)=>{
            const {chart} = context;
            const {ctx, chartArea} = chart;
            if(!chartArea) return hexToRgba(color, .25);
            return makeGradient(ctx, chartArea, color, .28);
          },
          tension: (type === 'line') ? 0.35 : 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: commonTooltip
        },
        scales: {
          x: { grid: { display:false } },
          y: {
            beginAtZero: true,
            ticks: {
              callback: (v)=> (v>=1e9? (v/1e9)+'B' : v>=1e6? (v/1e6)+'M' : v>=1e3? (v/1e3)+'k' : v)
            },
            grid: { color: 'rgba(0,0,0,.06)', borderDash: [4,4] }
          }
        }
      }
    });
  };

  const mkDoughnut = (canvasId, labels, data) => {
    const ctx = document.getElementById(canvasId).getContext('2d');
    // Màu ưu tiên theo nhãn đã chuẩn hóa; còn lại rơi vào palette
    const bg = labels.map((lbl, i)=> payColorMap[lbl] ?? palette[i % palette.length]);
    return new Chart(ctx, {
      type: 'doughnut',
      data: { labels, datasets: [{ data, backgroundColor: bg, borderWidth: 1 }]},
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
          legend: { position: 'bottom' },
          tooltip: commonTooltip
        }
      }
    });
  };

  // Khởi tạo
  const catChart   = mkCartesian('categoryRevenueChart', 'bar',  catLabels,     catRevenue,   PRIMARY);
  const dateChart  = mkCartesian('revenueByDateChart',  'line', revDateLabels,  revDateData,  '#6366f1');
  const monthChart = mkCartesian('revenueByMonthChart', 'bar',  revMonthLabels, revMonthData, '#22c55e');
  const yearChart  = mkCartesian('revenueByYearChart',  'bar',  revYearLabels,  revYearData,  '#f59e0b');
  const payChart   = mkDoughnut('revenueByPaymentMethodChart', payLabels, payRevenue);

  // ====== NÚT TẢI PNG ======
  document.querySelectorAll('[data-dl]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.getAttribute('data-dl');
      const canvas = document.getElementById(id);
      const a = document.createElement('a');
      a.download = `${id}.png`;
      a.href = canvas.toDataURL('image/png', 1.0);
      a.click();
    });
  });

  // ====== STAT PILLS ======
  document.getElementById('pill-30d').textContent = `30 ngày: ${fmtVND(sum(revDateData))}`;
  document.getElementById('pill-12m').textContent = `12 tháng: ${fmtVND(sum(revMonthData))}`;
  document.getElementById('pill-cats').textContent = `Theo danh mục: ${fmtVND(sum(catRevenue))}`;
  document.getElementById('pill-year').textContent = `Theo năm: ${fmtVND(sum(revYearData))}`;
});
</script>
@endsection
