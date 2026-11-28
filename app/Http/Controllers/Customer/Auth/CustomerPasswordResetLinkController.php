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
     * แสดงฟอร์มลืมรหัสผ่าน (รองรับทั้งการยืนยันชื่อ+เบอร์โทร/อีเมลเพื่อไปตั้งรหัสใหม่เองทันที และการส่งลิงก์ทางอีเมล)
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * ตรวจสอบข้อมูลเพื่อพาลูกค้าไปหน้าตั้งรหัสผ่านใหม่ด้วยตัวเองทันที หรือส่งลิงก์ทางอีเมล
     */
    public function store(Request $request): RedirectResponse
    {
        $identifier = CustomerRegisteredUserController::normalizeLoginIdentifier((string) $request->input('email', ''));
        $verifyName = trim((string) $request->input('name', ''));

        if ($identifier === '') {
            throw ValidationException::withMessages([
                'email' => 'กรุณากรอกเบอร์โทรศัพท์มือถือ หรือ อีเมลที่ใช้สมัครสมาชิก',
            ]);
        }

        // 1. ค้นหาในตารางสมาชิกหน้าเว็บ (Customer) ก่อน
        $customer = Customer::where('email', $identifier)->first();

        if ($customer) {
            // หากลูกค้ากรอก "ชื่อที่ใช้สมัคร" มาด้วย และตรงกับชื่อในระบบ -> สร้าง Token แล้วพาเด้งไปหน้าตั้งรหัสผ่านใหม่เองทันที!
            if ($verifyName !== '') {
                if (mb_strtolower(trim($customer->name)) === mb_strtolower($verifyName)) {
                    $token = Password::broker('customers')->createToken($customer);
                    return redirect()->route('password.reset', [
                        'token' => $token,
                        'email' => $customer->email,
                    ]);
                }

                throw ValidationException::withMessages([
                    'name' => 'ชื่อที่ระบุไม่ตรงกับข้อมูลสมาชิกของเบอร์โทร/อีเมลนี้ (หากจำชื่อไม่ได้ สามารถกดปุ่มทัก LINE ให้แอดมินส่งลิงก์ตั้งรหัสใหม่ให้ได้ทันทีครับ)',
                ]);
            }

            // กรณีไม่ได้กรอกชื่อมา แต่เป็นรูปแบบอีเมล -> ส่งลิงก์รีเซ็ตเข้าอีเมลตามปกติ
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                try {
                    $status = Password::broker('customers')->sendResetLink(['email' => $customer->email]);
                    if ($status == Password::RESET_LINK_SENT) {
                        return back()->with('status', 'ระบบได้ส่งลิงก์สำหรับตั้งรหัสผ่านใหม่ไปยังอีเมลของคุณแล้ว (หรือกรอกชื่อที่ใช้สมัครด้านล่างเพื่อตั้งรหัสใหม่บนหน้าเว็บทันที)');
                    }
                } catch (\Throwable $e) {
                    // กรณีเซิร์ฟเวอร์ยังไม่ได้ตั้งค่า SMTP ส่งเมล ให้แนะนำให้กรอกชื่อเพื่อรีเซ็ตบนหน้าเว็บทันที
                }
            }

            throw ValidationException::withMessages([
                'name' => 'กรุณากรอก "ชื่อที่ใช้สมัคร" ในช่องด้านล่าง เพื่อยืนยันตัวตนและไปหน้าตั้งรหัสผ่านใหม่ด้วยตัวเองทันทีครับ',
            ]);
        }

        // 2. กรณีเป็นผู้ใช้หลังบ้าน (User / Staff / Admin)
        $user = User::where('email', $identifier)->first();
        if ($user) {
            if ($verifyName !== '' && mb_strtolower(trim($user->name)) === mb_strtolower($verifyName)) {
                $token = Password::broker('users')->createToken($user);
                return redirect()->route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ]);
            }
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $status = Password::broker('users')->sendResetLink(['email' => $user->email]);
                if ($status == Password::RESET_LINK_SENT) {
                    return back()->with('status', __($status));
                }
            }
        }

        throw ValidationException::withMessages([
            'email' => 'ไม่พบข้อมูลสมาชิกที่ใช้เบอร์โทรศัพท์หรืออีเมลนี้ในระบบ',
        ]);
    }
}
