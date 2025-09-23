@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-lg-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="mb-3">Thanh toán PayOS (VietQR/MB)</h5>

        <div class="mb-2">Đơn hàng: <strong>#{{ $order->id }}</strong></div>
        <div class="mb-2">Số tiền: <strong class="text-danger">{{ number_format($order->total_price,0,',','.') }} đ</strong></div>
        <div class="mb-3">
          Nội dung CK:
          <code id="addInfoText">{{ $addInfo }}</code>
          <button class="btn btn-sm btn-outline-secondary" id="copyAddInfo">Sao chép</button>
        </div>

        {{-- Nút mở trang PayOS (checkoutUrl) --}}
        @if(!empty($payUrl) && filter_var($payUrl, FILTER_VALIDATE_URL))
          <div class="mb-3">
            <a href="{{ $payUrl }}" target="_blank" rel="noopener" class="btn btn-primary">
              Mở trang thanh toán PayOS
            </a>
          </div>
        @endif

        {{-- QR từ payload VietQR (data.qrCode) --}}
        @if(!empty($qrPayload))
          <div class="border rounded p-3 text-center">
            <div id="qrBox" class="d-inline-block"></div>
            <div class="small text-muted mt-2">Quét QR bằng ứng dụng ngân hàng để thanh toán.</div>
          </div>
        @else
          <div class="alert alert-warning">
            Không nhận được QR/Link từ PayOS. Vui lòng thử lại hoặc chọn phương thức khác.
          </div>
        @endif

        <div id="statusBox" class="alert alert-info mt-3">Đang chờ thanh toán…</div>
        <a href="{{ route('orders.show',$order) }}" class="btn btn-outline-secondary">Xem đơn</a>
      </div>
    </div>

    @if(!empty($rawJson))
    <div class="card mt-3">
      <div class="card-header">Debug (phản hồi từ PayOS)</div>
      <div class="card-body">
        <pre class="mb-0" style="white-space:pre-wrap;">{{ $rawJson }}</pre>
      </div>
    </div>
    @endif
  </div>
</div>

{{-- QR generator (thuần JS, không phụ thuộc CDN ngoài) --}}
<script>
// Tiny QR generator (qrcodejs) – bản rút gọn nhúng nội bộ:
!function(o){function r(o){this.mode=a,this.data=o}function e(o){this.mode=t,this.data=o}function n(o){this.totalCount=o,this.numDataCodewords=0,this.ecCodewordsPerBlock=0,this.ecBlocks=[]}function i(o){this.x=o%this.count,this.y=Math.floor(o/this.count)}var a=4,t=8;/* … (đoạn minified ~6KB bị lược để không dài chat) … */}();
</script>
<script>
(function(){
  // Copy nội dung CK
  const btn = document.getElementById('copyAddInfo');
  if (btn) {
    btn.addEventListener('click', () => {
      const v = document.getElementById('addInfoText').innerText.trim();
      navigator.clipboard.writeText(v).then(()=>btn.innerText='Đã sao chép');
    });
  }

  // Render QR nếu có payload VietQR
  @if(!empty($qrPayload))
    try {
      // qrcodejs (Tiny) – giả sử có constructor QRCode(container, options)
      new QRCode(document.getElementById('qrBox'), {
        text: {!! json_encode($qrPayload) !!},
        width: 240,
        height: 240
      });
    } catch(e) {}
  @endif

  // Poll trạng thái đơn
  const box = document.getElementById('statusBox');
  const url = "{{ route('orders.status',$order) }}";
  const timer = setInterval(async () => {
    try{
      const r = await fetch(url, {headers:{'Accept':'application/json'}});
      const d = await r.json();
      if(d.status === 'paid'){
        box.className = 'alert alert-success';
        box.textContent = 'Thanh toán thành công. Cảm ơn bạn!';
        clearInterval(timer);
      }
    }catch(e){}
  }, 5000);
})();
</script>
@endsection
