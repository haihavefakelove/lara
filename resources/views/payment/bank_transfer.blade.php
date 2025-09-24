@extends('layouts.app')

@section('content')

<div class="mb-3">
    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại đơn hàng
    </a>
</div>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="page-title mb-0">
        <i class="bi bi-credit-card me-2"></i>Thanh toán đơn hàng #{{ $order->id }}
    </h1>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>Quét mã QR để thanh toán</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <img src="{{ $qrUrl }}" 
                         alt="QR Code VietQR" 
                         class="img-fluid border rounded shadow-sm"
                         style="max-width: 280px;">
                </div>
                
                <div class="text-muted">
                    <i class="bi bi-1-circle-fill text-primary me-1"></i> Mở app Ngân hàng
                    <i class="bi bi-arrow-right mx-2"></i>
                    <i class="bi bi-2-circle-fill text-primary me-1"></i> Quét mã QR
                    <i class="bi bi-arrow-right mx-2"></i>
                    <i class="bi bi-3-circle-fill text-primary me-1"></i> Xác nhận
                </div>
                
                <div class="mt-4">
                    <div id="payment-status">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="spinner-border text-primary me-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div>
                                <h5 class="mb-1">Đang chờ thanh toán...</h5>
                                <p class="text-muted mb-0">Hệ thống sẽ tự động cập nhật khi nhận được thanh toán</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-bank me-2"></i>Thông tin chuyển khoản</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="fw-bold text-muted" width="140">Ngân hàng:</td>
                            <td class="fw-bold">MB Bank (MBBank)</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Chủ tài khoản:</td>
                            <td class="fw-bold">Vũ Đức Đạt</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Số tài khoản:</td>
                            <td>
                                <span class="fw-bold text-primary fs-5">0000153686666</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('0000153686666')" title="Sao chép">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Số tiền:</td>
                            <td class="fw-bold text-success fs-5">{{ number_format($order->total_price, 0, ',', '.') }} VNĐ</td>
                        </tr>
                        <tr>
                            <td class="fw-bold text-muted">Nội dung CK:</td>
                            <td>
                                <span class="fw-bold text-danger fs-5">{{ $orderCode }}</span>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $orderCode }}')" title="Sao chép">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Quan trọng:</strong> Vui lòng ghi chính xác nội dung "<strong>{{ $orderCode }}</strong>" để hệ thống tự động xác nhận thanh toán.
                </div>
            </div>
        </div>
    </div>


</div>
</div>



<script>
let checkInterval;


function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed';
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>Đã sao chép: ${text}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }).catch(() => {
        alert('Đã sao chép: ' + text);
    });
}

function checkPaymentStatus() {
    fetch(`{{ route('orders.status', $order) }}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'paid') {
                document.getElementById('payment-status').innerHTML = `
                    <div class="text-success">
                        <i class="bi bi-check-circle-fill fs-1 text-success mb-3"></i>
                        <h4 class="text-success mb-2">Thanh toán thành công!</h4>
                        <p class="text-muted">Đang chuyển hướng...</p>
                    </div>
                `;
                
                
                setTimeout(() => {
                    window.location.href = `{{ route('orders.thankyou', $order) }}`;
                }, 2500);
                
                if (checkInterval) {
                    clearInterval(checkInterval);
                }
            }
        })
        .catch(error => {
            console.error('Payment status check failed:', error);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    checkInterval = setInterval(checkPaymentStatus, 5000);
    setTimeout(() => {
        if (checkInterval) {
            clearInterval(checkInterval);
            document.getElementById('payment-status').innerHTML = `
                <div class="text-warning">
                    <i class="bi bi-clock-fill fs-2 mb-3"></i>
                    <h5 class="text-warning mb-2">Hết thời gian chờ thanh toán</h5>
                    <p class="text-muted">Vui lòng kiểm tra lại hoặc liên hệ hỗ trợ nếu bạn đã thanh toán.</p>
                </div>
            `;
        }
    }, 30 * 60 * 1000);
});
</script>
@endsection