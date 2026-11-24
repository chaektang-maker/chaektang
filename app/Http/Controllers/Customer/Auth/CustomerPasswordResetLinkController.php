<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerPasswordResetLinkController extends Controller
{
    /**
     * แสดงฟอร์มขอลิงก์รีเซ็ตรหัสผ่าน (รองรับทั้งลูกค้าและผู้ใช้หลังบ้าน)
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * ส่งอีเมลลิงก์รีเซ็ตรหัสผ่าน
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $broker = Customer::where('email', $email)->exists()
            ? 'customers'
            : (User::where('email', $email)->exists() ? 'users' : 'customers');

        $status = Password::broker($broker)->sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
