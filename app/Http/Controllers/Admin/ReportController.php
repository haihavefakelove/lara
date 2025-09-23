<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ReportController extends Controller
{
    public function index(Request $request)
    {
        
        /**
         * Chỉ tính doanh thu với đơn đã thanh toán / đã hoàn tất:
         *   - payment_status = 'paid'
         *   - OR status = 'paid'
         *   - OR shipping_status = 'completed'
         */
        $paidFilter = function($q) {
            $q->where('payment_status', 'paid')
              ->orWhere('status', 'paid')
              ->orWhere('shipping_status', 'completed');
        };

        // Doanh thu theo danh mục (lấy từ order_items)
        $categoryRevenue = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where(function($q) use ($paidFilter) {
                $paidFilter($q);
            })
            ->select(
                'products.category_id',
                DB::raw('SUM(order_items.price * order_items.quantity) AS total_revenue'),
                DB::raw('SUM(order_items.quantity) AS total_qty')
            )
            ->groupBy('products.category_id')
            ->orderByDesc('total_revenue')
            ->get();

        // Tổng số đơn & tổng số khách hàng
        $totalOrders    = Orders::count();
        $totalCustomers = DB::table('users')->where('role', 'customer')->count();

        // Doanh thu theo ngày
        $revenueByDate = Orders::query()
            ->where(function($q) use ($paidFilter) { $paidFilter($q); })
            ->selectRaw('DATE(created_at) AS date, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // Doanh thu theo tháng (YYYY-MM)
        $revenueByMonth = Orders::query()
            ->where(function($q) use ($paidFilter) { $paidFilter($q); })
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS month, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Doanh thu theo năm
        $revenueByYear = Orders::query()
            ->where(function($q) use ($paidFilter) { $paidFilter($q); })
            ->selectRaw('YEAR(created_at) AS year, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get();

        return view('admin.reports.index', compact(
            'categoryRevenue', 'totalOrders', 'totalCustomers',
            'revenueByDate', 'revenueByMonth', 'revenueByYear'
        ));
    }
    public function charts()
{
    // ======= 1) TIÊU CHÍ ĐƠN HÀNG ĐƯỢC TÍNH =======
    $paidStatuses = ['paid','paid_momo','cod_ordered','paid_cod'];

    // ======= 2) Doanh thu theo DANH MỤC =======
    $byCat = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->whereIn('orders.status', $paidStatuses)
        ->selectRaw('COALESCE(categories.name, CONCAT("Category #", products.category_id)) AS name')
        ->selectRaw('SUM(order_items.price * order_items.quantity) AS revenue')
        ->groupBy('name')
        ->orderByDesc('revenue')
        ->get();

    $catLabels = $byCat->pluck('name')->toArray();
    $catRevenue = $byCat->pluck('revenue')->map(fn($v) => (float)$v)->toArray();

    // ======= 3) Doanh thu theo NGÀY (30 ngày gần nhất) =======
    $startDay = Carbon::now()->subDays(29)->startOfDay();
    $endDay = Carbon::now()->endOfDay();

    $byDate = Orders::whereIn('status', $paidStatuses)
        ->whereBetween('created_at', [$startDay, $endDay])
        ->selectRaw('DATE(created_at) d, SUM(total_price) revenue')
        ->groupBy('d')
        ->orderBy('d')
        ->get()->keyBy('d');

    $revDateLabels = [];
    $revDateData = [];
    for ($i = 0; $i < 30; $i++) {
        $d = $startDay->copy()->addDays($i)->toDateString();
        $revDateLabels[] = $d;
        $revDateData[] = (float) ($byDate[$d]->revenue ?? 0);
    }

    // ======= 4) Doanh thu theo THÁNG (12 tháng gần nhất) =======
    $startMonth = Carbon::now()->subMonths(11)->startOfMonth();
    $byMonth = Orders::whereIn('status', $paidStatuses)
        ->where('created_at', '>=', $startMonth)
        ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') ym, SUM(total_price) revenue")
        ->groupBy('ym')
        ->orderBy('ym')
        ->get()->keyBy('ym');

    $revMonthLabels = [];
    $revMonthData = [];
    for ($i = 0; $i < 12; $i++) {
        $m = $startMonth->copy()->addMonths($i);
        $key = $m->format('Y-m');
        $revMonthLabels[] = $m->format('m/Y');
        $revMonthData[] = (float) ($byMonth[$key]->revenue ?? 0);
    }

    // ======= 5) Doanh thu theo NĂM =======
    $byYear = Orders::whereIn('status', $paidStatuses)
        ->selectRaw('YEAR(created_at) y, SUM(total_price) revenue')
        ->groupBy('y')
        ->orderBy('y')
        ->get();

    $revYearLabels = $byYear->pluck('y')->toArray();
    $revYearData = $byYear->pluck('revenue')->map(fn($v) => (float)$v)->toArray();

    // ======= 6) Doanh thu theo PHƯƠNG THỨC THANH TOÁN =======
    // Tính doanh thu cho phương thức thanh toán Momo, COD và PayOS
    $paymentMethodLabels = [ 'COD', 'MoMo', 'PayOS'];
    $paymentMethodRevenue = [
        
        (float) Orders::whereIn('status', ['paid', 'paid_cod', 'cod_ordered'])->sum('total_price'),
        (float) Orders::where('payment_method', 'momo')->where('status', 'paid')->sum('total_price'),  // Momo
        (float) Orders::where('payment_method', 'payos')->sum('total_price'),  // Thêm dữ liệu PayOS
    ];

    // Trả về view với các dữ liệu đã xử lý
    return view('admin.reports.charts', compact(
        'catLabels', 'catRevenue',
        'revDateLabels', 'revDateData',
        'revMonthLabels', 'revMonthData',
        'revYearLabels', 'revYearData',
        'paymentMethodLabels', 'paymentMethodRevenue'
    ));
}

}
