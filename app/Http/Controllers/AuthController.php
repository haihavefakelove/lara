<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Hiển thị form đăng ký.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký người dùng mới.
     * Role mặc định là "customer".
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
{
    // 1) Validate dữ liệu
    $validated = $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    try {
        // 2) Tạo người dùng mới (role mặc định: customer)
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'customer',
        ]);

        // 3) Gửi email xác thực
        // (User model cần implements MustVerifyEmail)
        if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
        }

        // 4) Đăng nhập luôn (để có thể vào trang /email/verify)
        Auth::login($user);

        // 5) Chuyển tới trang yêu cầu xác thực
        return redirect()
            ->route('verification.notice')
            ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực tài khoản.');

    } catch (\Throwable $e) {
        Log::error('Registration failed: ' . $e->getMessage());

        return back()
            ->withInput()
            ->with('error', 'Đăng ký thất bại, vui lòng thử lại sau.');
    }
}


    /**
     * Hiển thị form đăng nhập.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập người dùng.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required','string','email'],
            'password' => ['required','string'],
        ]);

        if (Auth::attempt($request->only('email','password'), $request->filled('remember'))) {
            $request->session()->regenerate();

            // Điều hướng theo vai trò
            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->intended(route('admin.dashboard'))
                                 ->with('success','Chào mừng quản trị viên!');
            }

            return redirect()->intended(route('shop.home'))
                             ->with('success','Đăng nhập thành công!');
        }

        throw ValidationException::withMessages([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ]);
    }
/**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success','Bạn đã đăng xuất thành công.');
    }
}
