<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\VipRequest;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VipRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        VipRequest::ensureColumnsExist();

        $status = $request->string('status')->toString();
        $search = $request->string('q')->toString();

        $query = VipRequest::query()
            ->with(['customer', 'approver']);

        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('bank_account_name', 'like', "%{$search}%")
                    ->orWhere('bank_account_number', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(function (VipRequest $req) {
                $data = $req->toArray();
                $extra = is_array($req->extra_slips) ? $req->extra_slips : [];
                $data['slip_count'] = 1 + count($extra);
                return $data;
            })
            ->withQueryString();

        $stats = [
            'total' => VipRequest::count(),
            'pending' => VipRequest::where('status', 'pending')->count(),
            'approved' => VipRequest::where('status', 'approved')->count(),
            'rejected' => VipRequest::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Admin/VipRequests/Index', [
            'requests' => $requests,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
            'stats' => $stats,
        ]);
    }

    public function show(VipRequest $vipRequest)
    {
        VipRequest::ensureColumnsExist();

        $vipRequest->load(['customer', 'approver']);

        $amount = (float) ($vipRequest->amount ?? 0);
        if ($amount >= 450) {
            $recommendedDays = 90;
            $packageLabel = 'แพ็กเกจ 3 เดือน (90 วัน)';
        } elseif ($amount >= 179) {
            $recommendedDays = 30;
            $packageLabel = 'แพ็กเกจรายเดือน (30 วัน)';
        } else {
            $recommendedDays = 15;
            $packageLabel = 'แพ็กเกจรายงวด (15 วัน)';
        }

        $activeSub = $vipRequest->customer?->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->orderByDesc('ends_at')
            ->first();

        // รวมรายการสลิปทั้งหมดในคำขอนี้ (สลิปใบแรก + สลิปที่ลูกค้าโอนเพิ่มใน extra_slips)
        $extraSlips = is_array($vipRequest->extra_slips) ? $vipRequest->extra_slips : [];
        $extraTotal = array_sum(array_map(fn ($s) => (float) ($s['amount'] ?? 0), $extraSlips));
        $firstSlipAmount = max(0, $amount - $extraTotal);

        $allSlips = [
            [
                'number' => 1,
                'label' => count($extraSlips) > 0 ? 'สลิปใบที่ 1 (โอนรอบแรก)' : 'สลิปการโอนเงิน',
                'url' => MediaStorageService::url($vipRequest->slip_path),
                'amount' => count($extraSlips) > 0 ? $firstSlipAmount : $amount,
                'created_at' => $vipRequest->created_at?->toIso8601String(),
            ],
        ];

        foreach ($extraSlips as $idx => $ex) {
            if (!empty($ex['path'])) {
                $allSlips[] = [
                    'number' => $idx + 2,
                    'label' => 'สลิปใบที่ ' . ($idx + 2) . ' (โอนเพิ่มให้ครบยอด)',
                    'url' => MediaStorageService::url($ex['path']),
                    'amount' => isset($ex['amount']) ? (float) $ex['amount'] : null,
                    'created_at' => $ex['created_at'] ?? null,
                ];
            }
        }

        $requestData = $vipRequest->toArray();
        $requestData['slip_url'] = MediaStorageService::url($vipRequest->slip_path);
        $requestData['all_slips'] = $allSlips;
        $requestData['slip_count'] = count($allSlips);
        $requestData['recommended_days'] = $recommendedDays;
        $requestData['package_label'] = $packageLabel;
        $requestData['customer_is_vip'] = $activeSub !== null;
        $requestData['customer_vip_ends_at'] = $activeSub?->ends_at?->toIso8601String();

        return Inertia::render('Admin/VipRequests/Show', [
            'request' => $requestData,
        ]);
    }

    public function approve(Request $request, VipRequest $vipRequest)
    {
        VipRequest::ensureColumnsExist();

        if ($vipRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'คำขอนี้ถูกจัดการไปแล้ว');
        }

        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $customer = $vipRequest->customer;
        if (!$customer) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลบัญชีสมาชิกของคำขอนี้ (บัญชีอาจถูกลบไปแล้ว)');
        }
        $now = now();

        // สร้างหรือขยาย subscription VIP (ลูกค้า)
        $currentSub = $customer->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', $now)
            ->orderByDesc('ends_at')
            ->first();

        if ($currentSub) {
            $endsAt = $currentSub->ends_at->copy()->addDays($data['days']);
            $currentSub->update([
                'ends_at' => $endsAt,
                'amount' => (float) $currentSub->amount + (float) ($vipRequest->amount ?? 0),
            ]);
        } else {
            Subscription::create([
                'customer_id' => $customer->id,
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

        // เคลียร์รายการ pending อื่นๆ ที่อาจค้างซ้ำซ้อนของลูกค้าคนเดียวกันออกอัตโนมัติ เพื่อไม่ให้แอดมินเผลอกดอนุมัติเบิ้ล
        VipRequest::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->where('id', '!=', $vipRequest->id)
            ->delete();

        return redirect()->route('backoffice.vip-requests.index')
            ->with('success', "อนุมัติ VIP ให้ {$customer->name} จำนวน {$data['days']} วัน สำเร็จ");
    }

    public function reject(Request $request, VipRequest $vipRequest)
    {
        VipRequest::ensureColumnsExist();

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
