<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(15);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:50|unique:coupons,code',
            'type'      => 'required|in:percent,fixed',
            'value'     => ['required','numeric','gt:0', Rule::when($request->type === 'percent', ['lte:100']), ],
            'max_uses'  => 'nullable|integer|min:1',
            'min_order' => Rule::when(
            $request->type === 'percent',
            ['required','numeric','min:0.01'],
            ['nullable','numeric','min:0']
            ),
            'max_order' => [
            'nullable','numeric','min:0',
                function ($attr, $value, $fail) use ($request) {
                    if ($value !== null && $request->filled('min_order')
                        && (float)$value < (float)$request->min_order) {
                        $fail('Đơn tối đa phải lớn hơn hoặc bằng Đơn tối thiểu.');
                    }
                },
            ],
            'start_at'  => 'nullable|date',
            'end_at'    => 'nullable|date|after_or_equal:start_at',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        Coupon::create($data);
        return redirect()->route('admin.coupons.index')->with('success', 'Tạo mã giảm giá thành công');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code'      => 'required|string|max:50|unique:coupons,code,'.$coupon->id,
            'type'      => 'required|in:percent,fixed',
            'value'     => ['required','numeric','gt:0', Rule::when($request->type === 'percent', ['lte:100']), ],
            'max_uses'  => 'nullable|integer|min:1',
            'min_order' => Rule::when(
            $request->type === 'percent',
            ['required','numeric','min:0.01'],
            ['nullable','numeric','min:0']
            ),
            'max_order' => [
            'nullable','numeric','min:0',
                function ($attr, $value, $fail) use ($request) {
                    if ($value !== null && $request->filled('min_order')
                        && (float)$value < (float)$request->min_order) {
                        $fail('Đơn tối đa phải lớn hơn hoặc bằng Đơn tối thiểu.');
                    }
                },
            ],
            'start_at'  => 'nullable|date',
            'end_at'    => 'nullable|date|after_or_equal:start_at',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $coupon->update($data);
        return redirect()->route('admin.coupons.index')->with('success', 'Cập nhật thành công');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Đã xoá mã');
    }
}
