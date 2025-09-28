<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Public / Shop
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\PageController;

// Admin
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\ChatbotController;

/*
|--------------------------------------------------------------------------
| SHOP (Public)
|--------------------------------------------------------------------------
*/
Route::get('/', [ShopController::class, 'index'])->name('shop.home');

Route::get('/products/{product}', [ProductController::class, 'show_normal'])
    ->name('products.show');

/* Recommendations */
Route::get('/recommendations/product/{productId}', [RecommendationController::class, 'product'])
    ->name('recommendations.product');
Route::get('/recommendations/home', [RecommendationController::class, 'home'])
    ->name('recommendations.home');

/*
|--------------------------------------------------------------------------
| STATIC PAGES (Public)
|--------------------------------------------------------------------------
*/
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register']);

Route::get('login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

/* Hủy đơn (public endpoint cho người dùng đã đăng nhập/owner trong controller) */
Route::middleware(['auth','verified'])->group(function(){
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

/*
|--------------------------------------------------------------------------
| EMAIL VERIFICATION
|--------------------------------------------------------------------------
*/
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('shop.home')->with('success', 'Xác thực email thành công!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Đã gửi lại email xác thực!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| ADMIN AREA (auth + verified + admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        /* CRUD */
        Route::resource('products',   AdminProductController::class);
        Route::resource('categories', AdminCategoryController::class);
        Route::resource('orders',     AdminOrderController::class)->only(['index','show','update']);
        Route::resource('users',      AdminUserController::class);
        Route::resource('coupons',    CouponController::class);

        /* Reports */
        Route::get('/reports',        [AdminReportController::class,'index'])->name('reports.index');
        Route::get('/reports/charts', [AdminReportController::class,'charts'])->name('reports.charts');

        /* Reviews (admin duyệt/xóa) */
        Route::get   ('/reviews',                [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::patch ('/reviews/{review}/status',[\App\Http\Controllers\Admin\ReviewController::class, 'updateStatus'])->name('reviews.updateStatus');
        Route::delete('/reviews/{review}',       [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

        /* Cập nhật trạng thái vận chuyển cho đơn */
        Route::patch('orders/{order}/shipping', [AdminOrderController::class, 'updateShippingStatus'])
            ->name('orders.update-shipping');

        /* Quản lý Trang tĩnh */
        Route::resource('pages', AdminPageController::class)->except(['show']);
    });

/*
|--------------------------------------------------------------------------
| CART (User)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {
    Route::get   ('/cart',                  [CartController::class, 'index'])->name('cart.index');
    Route::post  ('/cart/add/{product}',    [CartController::class, 'add'])->name('cart.add');
    Route::patch ('/cart/{id}',             [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post  ('/cart/apply-coupon',     [CartController::class, 'applyCoupon'])->name('cart.applyCoupon');
    Route::delete('/cart/remove-coupon',    [CartController::class, 'removeCoupon'])->name('cart.removeCoupon');

    /* Reviews theo từng item trong order */
    Route::get ('/orders/{order}/items/{item}/review',  [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/orders/{order}/items/{item}/review',  [ReviewController::class, 'store'])->name('reviews.store');
});

/*
|--------------------------------------------------------------------------
| ORDERS / PAYMENT (COD, MoMo) – yêu cầu đăng nhập
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','verified'])->group(function () {
    /* Orders */
    Route::get ('/orders',         [OrderController::class, 'index'])->name('orders.index');
    Route::get ('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders',         [OrderController::class, 'store'])->name('order.store');
    Route::get ('/orders/history', [OrderController::class, 'index']); // alias cũ

    /* Bank Transfer */
    Route::get ('/orders/{order}/pay/bank_transfer', [OrderController::class, 'payWithBankTransfer'])->name('orders.bank_transfer');

    /* Order status & completion */
    Route::get ('/orders/{order}/status',    [OrderController::class, 'status'])->name('orders.status');
    Route::get ('/orders/{order}/thankyou',  [OrderController::class, 'thankyou'])->name('orders.thankyou');
    Route::get ('/orders/{order}/cancelled', [OrderController::class, 'cancelled'])->name('orders.cancelled');

    /* (Nếu còn dùng) trang payment tổng quát */
    Route::get ('/payment',         [OrderController::class, 'index'])->name('payment.index');
    Route::post('/payment/process', [OrderController::class, 'processPayment'])->name('payment.process');

    /* MoMo */
    Route::get ('/momo/callback', [OrderController::class, 'momoCallback'])->name('momo.callback');
    Route::post('/momo/ipn',      [OrderController::class, 'momoIpn'])->name('momo.ipn');
    Route::get ('/orders/{order}/pay/momo', [OrderController::class, 'payAgain'])->name('orders.momo.pay');
});

/*
|--------------------------------------------------------------------------
| WEBHOOKS (PUBLIC – KHÔNG auth/CSRF)
|--------------------------------------------------------------------------
*/

// Webhook cho chuyển khoản ngân hàng
Route::post('/webhooks/bank-transfer', [OrderController::class, 'webhookBankTransfer'])->name('webhooks.bank_transfer');


Route::get('/chat', [ChatbotController::class, 'index'])->name('chat.index');
Route::post('/chat/message', [ChatbotController::class, 'message'])->name('chat.message');
