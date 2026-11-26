<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\VipRequest;
use App\Services\MediaStorageService;
use App\Services\TelegramNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VipRequestController extends Controller
{
    /**
     * แสดงฟอร์มขอเป็น VIP (แนบสลิปชำระเงิน)
     */
    public function create(Request $request): Response
    {
        VipRequest::ensureColumnsExist();

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
            ->get(['id', 'slip_path', 'extra_slips', 'amount', 'status', 'note', 'created_at', 'approved_at'])
            ->map(function (VipRequest $req) {
                $extra = is_array($req->extra_slips) ? $req->extra_slips : [];
                return [
                    'id' => $req->id,
                    'amount' => $req->amount,
                    'status' => $req->status,
                    'note' => $req->note,
                    'slip_count' => 1 + count($extra),
                    'created_at' => $req->created_at?->toIso8601String(),
                    'approved_at' => $req->approved_at?->toIso8601String(),
                ];
            })
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
     * - ป้องกันการสร้างแถว pending ซ้ำใน Backoffice (ลูกค้า 1 คนมีรายการ pending ได้แค่ 1 รายการ)
     * - รองรับโหมด 'append' (แนบสลิปโอนเพิ่มรอบ 2 ให้ครบยอด โดยบวกยอดเงินรวมและเก็บสลิปใบที่ 2 ไว้ในแถวเดียวกัน)
     * - รองรับโหมด 'replace' (เปลี่ยนรูปสลิปเดิมกรณีแนบผิดใบ)
     */
    public function store(Request $request): RedirectResponse
    {
        VipRequest::ensureColumnsExist();

        $validated = $request->validate([
            'slip' => ['required', 'file', 'image', 'max:5120'], // 5MB, รูปเท่านั้น
            'amount' => ['nullable', 'numeric', 'min:0'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:1000'],
            'submit_mode' => ['nullable', 'in:new,append,replace'],
        ]);

        $customer = $request->user('customer');
        $file = $request->file('slip');
        $fileHash = @hash_file('sha256', $file->getRealPath()) ?: null;
        $submitMode = $validated['submit_mode'] ?? 'new';

        // ตรวจสอบว่าไฟล์รูปสลิปนี้เคยถูกส่งในระบบแล้วหรือไม่ (ป้องกันการกดส่งรูปเดิมซ้ำ หรือเอาสลิปเก่ามาวนใช้)
        if ($fileHash) {
            $duplicateInMain = VipRequest::query()
                ->where('slip_hash', $fileHash)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            // กรณีโหมด replace อนุญาตถ้าเป็นคนละรูป แต่ถ้าเป็นรูปเดิมเป๊ะๆ ให้แจ้งเตือนว่าส่งไปแล้ว
            if ($duplicateInMain) {
                throw ValidationException::withMessages([
                    'slip' => 'ไฟล์รูปสลิปใบนี้ถูกส่งเข้าระบบเรียบร้อยแล้ว ไม่จำเป็นต้องส่งซ้ำครับ (หากโอนเพิ่มรอบ 2 กรุณาเลือกรูปสลิปใบที่โอนเพิ่ม)',
                ]);
            }

            // ตรวจใน extra_slips ของรายการที่ยัง pending อยู่
            $existingPendingForCheck = VipRequest::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->latest('id')
                ->first();

            if ($existingPendingForCheck && is_array($existingPendingForCheck->extra_slips)) {
                foreach ($existingPendingForCheck->extra_slips as $ex) {
                    if (($ex['hash'] ?? null) === $fileHash) {
                        throw ValidationException::withMessages([
                            'slip' => 'ไฟล์รูปสลิปใบนี้ถูกแนบไว้ในคำขอของคุณแล้ว ไม่จำเป็นต้องส่งซ้ำครับ',
                        ]);
                    }
                }
            }
        }

        $path = MediaStorageService::store($file, 'vip-slips', 'slip');

        // ตรวจสอบว่าลูกค้ามีรายการ pending ค้างอยู่แล้วหรือไม่
        $existingPending = VipRequest::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->first();

        if ($existingPending) {
            // ลบรายการ pending อื่นๆ ที่อาจเคยค้างซ้ำซ้อนของลูกค้าคนเดียวกันออกให้เหลือแค่แถวเดียว
            VipRequest::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'pending')
                ->where('id', '!=', $existingPending->id)
                ->delete();

            if ($submitMode === 'append') {
                // โหมดโอนเพิ่มรอบ 2 ให้ครบยอด: เก็บสลิปใบใหม่ต่อท้ายใน extra_slips และบวกยอดเงินรวมในแถวเดียว!
                $extraSlips = is_array($existingPending->extra_slips) ? $existingPending->extra_slips : [];
                $addedAmount = (float) ($validated['amount'] ?? 0);
                $oldAmount = (float) ($existingPending->amount ?? 0);
                $newTotalAmount = $oldAmount + $addedAmount;

                $extraSlips[] = [
                    'path' => $path,
                    'amount' => $addedAmount,
                    'hash' => $fileHash,
                    'note' => $validated['note'] ?? null,
                    'created_at' => now()->toIso8601String(),
                ];

                $slipNo = count($extraSlips) + 1;
                $updatedNote = trim((string) $existingPending->note);
                $appendText = "➕ [แนบสลิปโอนเพิ่มใบที่ {$slipNo}: +" . number_format($addedAmount, 2) . " บาท | ยอดโอนรวมทั้งหมด " . number_format($newTotalAmount, 2) . " บาท]";
                if (!empty($validated['note'])) {
                    $appendText .= " ({$validated['note']})";
                }
                $updatedNote = $updatedNote !== '' ? ($updatedNote . "\n" . $appendText) : $appendText;

                $existingPending->update([
                    'extra_slips' => $extraSlips,
                    'amount' => $newTotalAmount,
                    'bank_account_name' => $validated['bank_account_name'] ?: $existingPending->bank_account_name,
                    'bank_account_number' => $validated['bank_account_number'] ?: $existingPending->bank_account_number,
                    'note' => $updatedNote,
                ]);

                app(TelegramNotificationService::class)->sendVipRequestNotification($existingPending);

                return redirect()->route('vip-request.create')
                    ->with('success', "แนบสลิปโอนเพิ่มใบที่ {$slipNo} (+{$addedAmount} บาท) สำเร็จ! ยอดโอนรวมของคุณคือ " . number_format($newTotalAmount, 2) . " บาท");
            }

            // โหมด replace (หรือกรณีส่งใหม่ขณะยังมีรายการรอตรวจ): อัปเดตทับรายการ pending เดิม ไม่สร้างแถวที่ 2 ใน Backoffice
            $existingPending->update([
                'slip_path' => $path,
                'slip_hash' => $fileHash,
                'amount' => $validated['amount'] ?? $existingPending->amount,
                'bank_account_name' => $validated['bank_account_name'] ?: $existingPending->bank_account_name,
                'bank_account_number' => $validated['bank_account_number'] ?: $existingPending->bank_account_number,
                'note' => $validated['note'] ?: $existingPending->note,
            ]);

            app(TelegramNotificationService::class)->sendVipRequestNotification($existingPending);

            return redirect()->route('vip-request.create')
                ->with('success', 'อัปเดตสลิปการโอนเงินของคุณเรียบร้อยแล้ว ทีมงานกำลังตรวจสอบและเปิดสิทธิ์ให้คุณ');
        }

        // กรณียังไม่มีรายการ pending ค้างอยู่: สร้างรายการใหม่ 1 รายการ
        $vipRequest = VipRequest::create([
            'customer_id' => $customer->id,
            'slip_path' => $path,
            'extra_slips' => [],
            'slip_hash' => $fileHash,
            'amount' => $validated['amount'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);

        // ส่งแจ้งเตือนพร้อมรูปสลิปเข้า Telegram ของแอดมินทันที
        app(TelegramNotificationService::class)->sendVipRequestNotification($vipRequest);

        return redirect()->route('vip-request.create')
            ->with('success', 'ส่งคำขอ VIP เรียบร้อยแล้ว ทีมงานจะตรวจสอบและแจ้งผลให้คุณ');
    }
}
