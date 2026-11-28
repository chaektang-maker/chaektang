<?php

namespace App\Http\Controllers\Customer\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerRegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * จัดรูปแบบค่าที่กรอกในช่อง email (รองรับทั้งอีเมล และ เบอร์โทรศัพท์มือถือ 10 หลัก)
     */
    public static function normalizeLoginIdentifier(string $raw): string
    {
        $trimmed = trim(mb_strtolower($raw));
        // ถ้าไม่มี @ และมีตัวเลขเป็นหลัก (เช่น 089-123-4567) ให้ตัดขีดและช่องว่างออกเหลือเฉพาะตัวเลข
        if (!str_contains($trimmed, '@')) {
            $digits = preg_replace('/[\s\-]/', '', $trimmed);
            if (preg_match('/^0\d{8,9}$/', $digits)) {
                return $digits;
            }
        }
        return $trimmed;
    }

    public function store(Request $request): RedirectResponse
    {
        $normalizedIdentifier = self::normalizeLoginIdentifier((string) $request->input('email', ''));
        $request->merge(['email' => $normalizedIdentifier]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'กรุณากรอกเบอร์โทรศัพท์มือถือ หรือ อีเมล',
            'email.unique' => 'เบอร์โทรศัพท์หรืออีเมลนี้ถูกใช้สมัครสมาชิกไปแล้ว สามารถกดเข้าสู่ระบบหรือตั้งรหัสผ่านใหม่ได้เลยครับ',
        ]);

        // ตรวจสอบว่าเป็นอีเมลที่ถูกต้อง หรือ เบอร์โทรศัพท์ 9-10 หลัก
        $isEmail = filter_var($normalizedIdentifier, FILTER_VALIDATE_EMAIL) !== false;
        $isPhone = preg_match('/^0\d{8,9}$/', $normalizedIdentifier) === 1;

        if (!$isEmail && !$isPhone) {
            throw ValidationException::withMessages([
                'email' => 'กรุณากรอกเบอร์โทรศัพท์มือถือ 10 หลัก (เช่น 0891234567) หรือ อีเมลให้ถูกต้อง',
            ]);
        }

        $customer = Customer::create([
            'name' => trim($request->name),
            'email' => $normalizedIdentifier,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($customer));

        Auth::guard('customer')->login($customer);

        return redirect()->intended(route('home'));
    }
}
