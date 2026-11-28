<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\Customer;
use App\Models\LotteryNumber;
use App\Models\LotteryResult;
use App\Models\LottoData;
use App\Models\Post;
use App\Models\Source;
use App\Models\Subscription;
use App\Models\VipRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('web');
        // ป้องกันเจ้าสำนักเข้าดูหน้า Dashboard รายได้ของแอดมิน
        if ($user && $user->isSourceOwner() && !$user->isAdmin()) {
            return redirect()->route('backportal.index');
        }

        $now = now();

        // 1. VIP & Revenue Stats
        $pendingVipRequestsCount = VipRequest::where('status', 'pending')->count();
        $activeVipCount = Customer::whereHas('subscriptions', function ($q) use ($now) {
            $q->where('status', 'active')->where('ends_at', '>', $now);
        })->count();

        $monthlyVipRevenue = (float) Subscription::where('status', 'active')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('amount');

        $totalVipRevenue = (float) Subscription::where('status', 'active')->sum('amount');

        // 2. Customer Stats
        $totalCustomers = Customer::count();
        $todayCustomers = Customer::whereDate('created_at', $now->toDateString())->count();

        // 3. Sources & Lottery Stats
        $activeSourcesCount = Source::where('status', 'active')->count();
        $pendingOwnerSourcesCount = Source::where('status', '!=', 'active')
            ->whereHas('owners')
            ->count();

        $promotedSources = Source::where('status', 'active')
            ->where('is_promoted', true)
            ->orderBy('promoted_until')
            ->get()
            ->map(fn (Source $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'promoted_until' => $s->promoted_until?->format('Y-m-d'),
                'is_currently_promoted' => $s->isCurrentlyPromoted(),
                'view_count' => (int) ($s->view_count ?? 0),
                'popularity_score' => (int) ($s->popularity_score ?? 0),
            ]);

        $latestLottoData = LottoData::whereNotNull('draw_date')
            ->orderByDesc('draw_date')
            ->first();

        $latestDrawDate = $latestLottoData?->draw_date instanceof \DateTimeInterface
            ? $latestLottoData->draw_date->format('Y-m-d')
            : ($latestLottoData?->draw_date ? substr((string) $latestLottoData->draw_date, 0, 10) : null);

        $latestDrawSourcesSubmitted = $latestDrawDate
            ? LotteryNumber::where('draw_date', $latestDrawDate)
                ->whereHas('source', fn ($q) => $q->where('status', 'active'))
                ->distinct('source_id')
                ->count('source_id')
            : 0;

        $pendingLottoDetailsCount = LottoData::where('is_fetched', false)->count();
        $uncalculatedResultsCount = LotteryResult::where('is_calculated', false)->count();

        // 4. Recent Pending VIP Requests (for quick action)
        $recentPendingVipRequests = VipRequest::with('customer')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (VipRequest $r) => [
                'id' => $r->id,
                'customer_name' => $r->customer?->name ?? '-',
                'customer_email' => $r->customer?->email ?? '-',
                'amount' => (float) ($r->amount ?? 0),
                'bank_account_name' => $r->bank_account_name,
                'created_at' => $r->created_at?->toIso8601String(),
            ]);

        // 5. Content & Affiliate Stats
        $activeProductsCount = AffiliateProduct::where('is_active', true)->count();
        $publishedPostsCount = Post::where('is_published', true)->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'pending_vip_requests_count' => $pendingVipRequestsCount,
                'active_vip_count' => $activeVipCount,
                'monthly_vip_revenue' => $monthlyVipRevenue,
                'total_vip_revenue' => $totalVipRevenue,
                'total_customers' => $totalCustomers,
                'today_customers' => $todayCustomers,
                'active_sources_count' => $activeSourcesCount,
                'pending_owner_sources_count' => $pendingOwnerSourcesCount,
                'promoted_sources_count' => $promotedSources->where('is_currently_promoted', true)->count(),
                'latest_draw_date' => $latestDrawDate,
                'latest_draw_date_text' => $latestLottoData?->date_text,
                'latest_draw_sources_submitted' => $latestDrawSourcesSubmitted,
                'pending_lotto_details_count' => $pendingLottoDetailsCount,
                'uncalculated_results_count' => $uncalculatedResultsCount,
                'active_products_count' => $activeProductsCount,
                'published_posts_count' => $publishedPostsCount,
            ],
            'recentPendingVipRequests' => $recentPendingVipRequests,
            'promotedSources' => $promotedSources,
        ]);
    }
}

