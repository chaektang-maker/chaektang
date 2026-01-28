<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Admin เข้าถึงได้ทุกอย่าง
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Dashboard เข้าถึงได้ทุกคนที่ login แล้ว
        $routeName = Route::currentRouteName();
        if ($routeName === 'backoffice.dashboard') {
            return $next($request);
        }

        // Staff ต้องเช็ค permission จาก route name
        if ($routeName && !$user->canAccessRoute($routeName)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
