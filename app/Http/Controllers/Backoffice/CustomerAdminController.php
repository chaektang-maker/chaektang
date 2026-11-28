<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class CustomerAdminController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $search = $request->string('q')->toString();
        $vipStatus = $request->string('vip_status')->toString();

        $query = Customer::query()
            ->withCount(['vipRequests', 'userVotes'])
            ->with([
                'subscriptions' => function ($q) use ($now) {
                    $q->where('status', 'active')
                        ->where('ends_at', '>', $now)
                        ->orderByDesc('ends_at');
                },
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($vipStatus === 'vip') {
            $query->whereHas('subscriptions', function ($q) use ($now) {
                $q->where('status', 'active')->where('ends_at', '>', $now);
            });
        } elseif ($vipStatus === 'free') {
            $query->whereDoesntHave('subscriptions', function ($q) use ($now) {
                $q->where('status', 'active')->where('ends_at', '>', $now);
            });
        }

        $customers = $query
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(function (Customer $customer) {
                $activeSub = $customer->subscriptions->first();

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'created_at' => $customer->created_at?->toIso8601String(),
                    'is_vip' => $activeSub !== null,
                    'vip_ends_at' => $activeSub?->ends_at?->toIso8601String(),
                    'vip_days_remaining' => $activeSub?->ends_at
                        ? max(0, (int) ceil(now()->diffInHours($activeSub->ends_at, false) / 24))
                        : 0,
                    'vip_requests_count' => $customer->vip_requests_count,
                    'user_votes_count' => $customer->user_votes_count,
                ];
            });

        $totalCustomers = Customer::count();
        $vipCustomers = Customer::whereHas('subscriptions', function ($q) use ($now) {
            $q->where('status', 'active')->where('ends_at', '>', $now);
        })->count();

        return Inertia::render('Backoffice/Customers/Index', [
            'customers' => $customers,
            'filters' => [
                'q' => $search,
                'vip_status' => $vipStatus,
            ],
            'stats' => [
                'total' => $totalCustomers,
                'vip' => $vipCustomers,
                'free' => max(0, $totalCustomers - $vipCustomers),
            ],
        ]);
    }

    public function grantVip(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:999999'],
        ]);

        $now = now();
        $days = (int) $data['days'];
        $amount = (float) ($data['amount'] ?? 0);

        $currentSub = $customer->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', $now)
            ->orderByDesc('ends_at')
            ->first();

        if ($currentSub) {
            $currentSub->update([
                'ends_at' => $currentSub->ends_at->copy()->addDays($days),
                'amount' => (float) $currentSub->amount + $amount,
            ]);
        } else {
            Subscription::create([
                'customer_id' => $customer->id,
                'plan' => 'vip',
                'amount' => $amount,
                'starts_at' => $now,
                'ends_at' => $now->copy()->addDays($days),
                'status' => 'active',
                'payment_method' => 'manual_admin',
                'transaction_id' => null,
            ]);
        }

        return redirect()->back()->with('success', "เพิ่มสิทธิ์ VIP ให้ {$customer->name} จำนวน {$days} วัน เรียบร้อยแล้ว");
    }

    public function revokeVip(Request $request, Customer $customer)
    {
        $now = now();

        $customer->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', $now)
            ->update([
                'status' => 'cancelled',
                'ends_at' => $now,
            ]);

        return redirect()->back()->with('success', "ยกเลิกสถานะ VIP ของ {$customer->name} เรียบร้อยแล้ว");
    }

    /**
     * สร้างลิงก์ให้ลูกค้ากดไปตั้งรหัสผ่านใหม่ด้วยตัวเอง (สำหรับแอดมินก๊อปปี้ส่งให้ลูกค้าทาง LINE)
     */
    public function generateResetLink(Request $request, Customer $customer)
    {
        $token = Password::broker('customers')->createToken($customer);
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $customer->email,
        ]);

        return redirect()->back()
            ->with('success', "สร้างลิงก์ตั้งรหัสผ่านใหม่สำหรับ {$customer->name} เรียบร้อยแล้ว คัดลอกลิงก์ด้านล่างส่งให้ลูกค้าได้เลยครับ")
            ->with('generated_reset_link', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_email' => $customer->email,
                'url' => $resetUrl,
            ]);
    }

    /**
     * แอดมินตั้งรหัสผ่านใหม่ให้ลูกค้าโดยตรง (กรณีลูกค้าผู้สูงอายุขอให้แอดมินตั้งรหัสให้)
     */
    public function resetPassword(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        $customer->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->back()->with('success', "เปลี่ยนรหัสผ่านใหม่ให้ {$customer->name} เรียบร้อยแล้ว");
    }
}

