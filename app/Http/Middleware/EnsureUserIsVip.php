<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVip
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = $request->user('customer');
        $adminUser = $request->user('web');

        // อนุญาตให้แอดมิน (guard web) เข้าดูได้ หรือลูกค้า (guard customer) ที่เป็นสมาชิก VIP
        if ($adminUser && $adminUser->isAdmin()) {
            return $next($request);
        }

        if (!$customer || !$customer->isVip()) {
            abort(403, 'ต้องเป็นสมาชิก VIP เท่านั้นจึงจะเข้าถึงได้');
        }

        return $next($request);
    }
}

