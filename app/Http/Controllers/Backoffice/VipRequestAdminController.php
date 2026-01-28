<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\VipRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VipRequestAdminController extends Controller
{
    public function index()
    {
        $requests = VipRequest::query()
            ->with(['user', 'approver'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Admin/VipRequests/Index', [
            'requests' => $requests,
        ]);
    }

    public function show(VipRequest $vipRequest)
    {
        $vipRequest->load(['user', 'approver']);

        return Inertia::render('Admin/VipRequests/Show', [
            'request' => $vipRequest,
        ]);
    }

    public function approve(Request $request, VipRequest $vipRequest)
    {
        if ($vipRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'คำขอนี้ถูกจัดการไปแล้ว');
        }

        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $user = $vipRequest->user;

        $now = now();

        // สร้างหรือขยาย subscription VIP
        $currentSub = $user->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', $now)
            ->first();

        if ($currentSub) {
            $endsAt = $currentSub->ends_at->copy()->addDays($data['days']);
            $currentSub->update([
                'ends_at' => $endsAt,
            ]);
        } else {
            Subscription::create([
                'user_id' => $user->id,
                'plan' => 'vip',
                'amount' => $vipRequest->amount ?? 0,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addDays($data['days']),
                'status' => 'active',
                'payment_method' => 'bank_transfer',
                'transaction_id' => null,
            ]);
        }

        $vipRequest->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('backoffice.vip-requests.index')
            ->with('success', 'อนุมัติ VIP สำเร็จ');
    }

    public function reject(Request $request, VipRequest $vipRequest)
    {
        if ($vipRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'คำขอนี้ถูกจัดการไปแล้ว');
        }

        $vipRequest->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('backoffice.vip-requests.index')
            ->with('success', 'ปฏิเสธคำขอ VIP เรียบร้อยแล้ว');
    }
}

