<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\VipRequest;
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
    public function create(): Response
    {
        return Inertia::render('VipRequest/Create', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
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
        $path = $file->store('vip-slips', 'public');

        VipRequest::create([
            'customer_id' => $request->user('customer')->id,
            'slip_path' => $path,
            'amount' => $validated['amount'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('vip-request.create')
            ->with('success', 'ส่งคำขอ VIP เรียบร้อยแล้ว ทีมงานจะตรวจสอบและแจ้งผลให้คุณ');
    }
}
