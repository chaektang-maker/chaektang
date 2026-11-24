<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user('web'); // Backoffice (users table)
        $customer = $request->user('customer'); // ลูกค้า (customers table)

        $canAccess = [];
        $isAdmin = false;

        if ($user) {
            $isAdmin = $user->isAdmin();

            if ($isAdmin) {
                $canAccess = [
                    'sources' => true,
                    'results' => true,
                    'numbers' => true,
                    'lottoData' => true,
                    'lottoDetails' => true,
                ];
            } else {
                $canAccess = [
                    'sources' => $user->canAccessRoute('backoffice.sources.index'),
                    'results' => $user->canAccessRoute('backoffice.results.index'),
                    'numbers' => $user->canAccessRoute('backoffice.numbers.index'),
                    'lottoData' => $user->canAccessRoute('backoffice.lotto-data.index'),
                    'lottoDetails' => $user->canAccessRoute('backoffice.lotto-details-admin.index'),
                ];
            }
        }

        $pendingVipRequestsCount = 0;
        if ($user && $isAdmin) {
            $pendingVipRequestsCount = \App\Models\VipRequest::where('status', 'pending')->count();
        }

        $activeCustomerSub = null;
        $vipDaysLeft = 0;
        if ($customer) {
            $activeCustomerSub = $customer->subscriptions()
                ->where('status', 'active')
                ->where('ends_at', '>', now())
                ->orderByDesc('ends_at')
                ->first();
            if ($activeCustomerSub && $activeCustomerSub->ends_at) {
                $vipDaysLeft = (int) max(1, ceil(now()->diffInHours($activeCustomerSub->ends_at, false) / 24));
            }
        }

        return [
            ...parent::share($request),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'dream_interpretation' => $request->session()->get('dream_interpretation'),
                'dream_error' => $request->session()->get('dream_error'),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'source_id' => $user->source_id,
                    'is_source_owner' => $user->isSourceOwner(),
                    'is_admin' => $isAdmin,
                    'can_access' => $canAccess,
                    'pending_vip_requests_count' => $pendingVipRequestsCount,
                ] : null,
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'is_vip' => (bool) $activeCustomerSub,
                    'vip_ends_at' => $activeCustomerSub?->ends_at?->toIso8601String(),
                    'vip_days_left' => $vipDaysLeft,
                ] : null,
            ],
            'contactInfo' => config('services.contact'),
        ];
    }
}
