<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\Source;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BackportalRegisteredUserController extends Controller
{
    /**
     * แสดงหน้าสมัครเปิดบัญชีเจ้าสำนัก / ร้านค้าพาร์ทเนอร์ (/backportal/register)
     */
    public function create(): Response
    {
        return Inertia::render('Auth/BackportalRegister');
    }

    /**
     * บันทึกการสมัครเจ้าสำนักใหม่ (สร้าง Source สถานะ suspended ซ่อนไว้ก่อนรอแอดมินเปิด + สร้าง User ผูก source_id)
     */
    public function store(Request $request): RedirectResponse
    {
        $rawLogin = trim((string) $request->input('email', ''));
        $digitsOnly = preg_replace('/[^0-9]/', '', $rawLogin);

        if (!str_contains($rawLogin, '@')) {
            if (!preg_match('/^0[0-9]{9}$/', $digitsOnly)) {
                throw ValidationException::withMessages([
                    'email' => 'กรุณากรอกเบอร์โทรศัพท์มือถือ 10 หลัก (เช่น 0812345678) หรืออีเมลให้ถูกต้อง',
                ]);
            }
            $normalizedIdentifier = $digitsOnly;
        } else {
            if (!filter_var($rawLogin, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'email' => 'รูปแบบอีเมลไม่ถูกต้อง',
                ]);
            }
            $normalizedIdentifier = Str::lower($rawLogin);
        }

        $request->merge([
            'email' => $normalizedIdentifier,
            'source_name' => trim((string) $request->input('source_name', '')),
        ]);

        $validated = $request->validate([
            'source_name' => ['required', 'string', 'max:255', 'unique:sources,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'source_name.required' => 'กรุณากรอกชื่อสำนักหวย หรือชื่อเพจ/ร้านค้าของคุณ',
            'source_name.unique' => 'ชื่อสำนักนี้มีอยู่ในระบบแล้ว หากคุณเป็นเจ้าของสำนักเดิม กรุณาติดต่อแอดมินเพื่อรับสิทธิ์ดูแลสำนัก',
            'name.required' => 'กรุณากรอกชื่อผู้ดูแลสำนัก / ชื่ออาจารย์',
            'email.required' => 'กรุณากรอกเบอร์โทรศัพท์ หรือ อีเมล สำหรับใช้เข้าสู่ระบบ',
            'email.unique' => 'เบอร์โทรศัพท์หรืออีเมลนี้ถูกใช้สมัครในระบบแล้ว กรุณาเข้าสู่ระบบแทน',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // สร้างสำนักใหม่โดยตั้งค่า status = 'suspended' (ซ่อนไว้ก่อน รอแอดมินตรวจสอบและกดเปิดแสดงผล)
            $source = Source::create([
                'name' => $validated['source_name'],
                'description' => trim((string) ($validated['description'] ?? '')) ?: null,
                'status' => 'suspended',
                'popularity_score' => 0,
                'is_promoted' => false,
                'view_count' => 0,
            ]);

            // สร้าง Platform ร้านค้าที่ผูกกับสำนักไว้รองรับการลงสินค้าสายมู (ซ่อนไว้ก่อนจนกว่าแอดมินจะกดเปิดแสดงสำนัก)
            Platform::firstOrCreate(
                ['slug' => 'source-' . $source->id],
                [
                    'name' => $source->name,
                    'is_active' => false,
                    'sort_order' => 0,
                ]
            );

            return User::create([
                'name' => trim($validated['name']),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'staff',
                'source_id' => $source->id,
            ]);
        });

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('backportal.index')
            ->with('success', 'สมัครเปิดบัญชีเจ้าสำนักสำเร็จ! คุณสามารถลงเลขเด็ดและสินค้าไว้ล่วงหน้าได้ทันที (สำนักจะแสดงบนหน้าเว็บเมื่อแอดมินตรวจสอบและกดเปิดแสดงผล)');
    }
}
