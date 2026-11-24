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
        $user = $request->user('web');

        // อนุญาตเฉพาะพนักงาน (admin หรือ staff) ในตาราง users เท่านั้น ห้ามลูกค้าหรือบทบาทอื่นเข้าหลังบ้าน
        if (!$user || (!$user->isAdmin() && !$user->isStaff())) {
            abort(403, 'เฉพาะผู้ดูแลระบบและพนักงานเท่านั้นที่สามารถเข้าใช้งานระบบหลังบ้านได้');
        }

        // Admin เข้าถึงได้ทุกอย่าง
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Dashboard เข้าถึงได้เฉพาะพนักงาน (admin/staff) ที่ login แล้ว
        $routeName = Route::currentRouteName();
        if ($routeName === 'backoffice.dashboard') {
            return $next($request);
        }

        // Staff ต้องเช็ค permission จาก route name
        if ($routeName && !$user->canAccessRoute($routeName)) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงเมนูนี้ กรุณาติดต่อผู้ดูแลระบบ');
        }

        return $next($request);
    }
}
