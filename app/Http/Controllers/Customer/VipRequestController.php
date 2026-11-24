<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\VipRequest;
use App\Services\MediaStorageService;
use App\Services\TelegramNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class VipRequestController extends Controller
{
    /**
     * แสดงฟอร์มขอเป็น VIP (แนบสลิปชำระเงิน)
     */
    public function create(Request $request): Response
    {
        $customer = $request->user('customer');

        if (! $customer) {
            // จำหน้า /request-vip ไว้เพื่อให้หลังล็อกอินหรือสมัครสมาชิกเสร็จ เด้งกลับมาหน้านี้ทันที
            $request->session()->put('url.intended', route('vip-request.create'));
        }

        $activeSubscription = $customer?->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->orderByDesc('ends_at')
            ->first();

        $recentRequests = $customer?->vipRequests()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'amount', 'status', 'note', 'created_at', 'approved_at'])
            ?? collect();

        return Inertia::render('VipRequest/Create', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'isLoggedIn' => (bool) $customer,
            'paymentConfig' => config('services.vip_payment'),
            'activeSubscription' => $activeSubscription ? [
                'plan' => $activeSubscription->plan,
                'ends_at' => $activeSubscription->ends_at?->toIso8601String(),
            ] : null,
            'recentRequests' => $recentRequests,
        ]);
    }

    /**
     * บันทึกคำขอ VIP พร้อมสลิป
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slip' => ['required', 'file', 'image', 'max:5120'], // 5MB, รูปเท่านั้น
            'amount' => ['nullable', 'numeric', 'min:0'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $file = $request->file('slip');
        $path = MediaStorageService::store($file, 'vip-slips', 'slip');

        $vipRequest = VipRequest::create([
            'customer_id' => $request->user('customer')->id,
            'slip_path' => $path,
            'amount' => $validated['amount'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);

        // ส่งแจ้งเตือนพร้อมรูปสลิปเข้า Telegram ของแอดมินทันที (ถ้าตั้งค่า TELEGRAM_BOT_TOKEN และ TELEGRAM_CHAT_ID ไว้)
        app(TelegramNotificationService::class)->sendVipRequestNotification($vipRequest);

        return redirect()->route('vip-request.create')
            ->with('success', 'ส่งคำขอ VIP เรียบร้อยแล้ว ทีมงานจะตรวจสอบและแจ้งผลให้คุณ');
    }
}
