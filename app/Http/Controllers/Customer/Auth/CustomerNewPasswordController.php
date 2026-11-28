<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerNewPasswordController extends Controller
{
    /**
     * แสดงฟอร์มตั้งรหัสผ่านใหม่ด้วยตัวเอง (รองรับทั้งเบอร์โทรศัพท์และอีเมล)
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => (string) $request->query('email', $request->email ?? ''),
            'token' => (string) $request->route('token'),
        ]);
    }

    /**
     * บันทึกรหัสผ่านใหม่
     */
    public function store(Request $request): RedirectResponse
    {
        $normalizedIdentifier = CustomerRegisteredUserController::normalizeLoginIdentifier((string) $request->input('email', ''));
        $request->merge(['email' => $normalizedIdentifier]);

        $request->validate([
            'token' => 'required',
            'email' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->input('email');
        $isCustomer = Customer::where('email', $email)->exists();
        $broker = $isCustomer ? 'customers' : (User::where('email', $email)->exists() ? 'users' : 'customers');

        $status = Password::broker($broker)->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($account) use ($request) {
                $account->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($account));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            $loginRoute = ($broker === 'users') ? 'backoffice.login' : 'login';
            return redirect()->route($loginRoute)->with('status', 'ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว! กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่ของคุณ');
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
