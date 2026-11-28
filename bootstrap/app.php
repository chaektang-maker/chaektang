<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // แยก Path Login ชัดเจน: ถ้าเข้า /backoffice/* ให้เด้งไป /backoffice/login เท่านั้น ส่วนหน้าบ้านไป /login
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('backoffice') || $request->is('backoffice/*')) {
                return route('backoffice.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // จัดการ 404 errors สำหรับ Inertia
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'ไม่พบหน้านี้'], 404);
            }

            return \Inertia\Inertia::render('Errors/404', [
                'status' => 404,
                'canLogin' => \Illuminate\Support\Facades\Route::has('login'),
                'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
            ])->toResponse($request)->setStatusCode(404);
        });

        // ป้องกันไม่ให้แสดง Internal Error / Stack Trace / ชื่อไฟล์โค้ด ออกทางหน้าจอเด็ดขาด
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // ปล่อยให้ Laravel จัดการ Validation, Authentication, Redirect ตามปกติ
            if (
                $e instanceof \Illuminate\Validation\ValidationException ||
                $e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Illuminate\Http\Exceptions\HttpResponseException ||
                $e instanceof \Illuminate\Session\TokenMismatchException
            ) {
                return null;
            }

            $status = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            if ($status === 404) {
                return null;
            }

            $titles = [
                403 => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้',
                419 => 'หน้าเว็บหมดอายุ กรุณารีเฟรชหน้าจอ',
                429 => 'ทำรายการถี่เกินไป กรุณารอสักครู่',
                500 => 'ระบบขัดข้องชั่วคราว',
                503 => 'ระบบกำลังปิดปรับปรุงชั่วคราว',
            ];

            $messages = [
                403 => ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getMessage())
                    ? $e->getMessage()
                    : 'บัญชีของคุณไม่มีสิทธิ์เข้าใช้งานในส่วนนี้',
                419 => 'เซสชันการใช้งานหมดอายุ กรุณารีเฟรชหน้าเว็บแล้วลองใหม่อีกครั้ง',
                429 => 'คุณทำรายการเร็วเกินไป กรุณารอสักครู่แล้วลองใหม่อีกครั้ง',
                500 => 'ขออภัย เกิดข้อผิดพลาดในการประมวลผล ระบบได้บันทึกรายงานปัญหานี้เรียบร้อยแล้ว กรุณาลองใหม่อีกครั้ง',
                503 => 'ระบบกำลังอัปเดตข้อมูลชั่วคราว กรุณารอสักครู่แล้วเข้าใช้งานใหม่',
            ];

            $title = $titles[$status] ?? 'เกิดข้อผิดพลาดในการทำรายการ';
            $friendlyMessage = $messages[$status] ?? $messages[500];

            // กรณีเรียกผ่าน API / Fetch JSON
            if (($request->expectsJson() || $request->is('api/*')) && !$request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => $friendlyMessage,
                ], $status);
            }

            // กรณีกดปุ่ม Submit ฟอร์ม (POST / PUT / PATCH / DELETE) ให้เด้งกลับหน้าเดิมพร้อมข้อความแจ้งเตือนสุภาพ
            if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
                return back()->with('error', $friendlyMessage);
            }

            $isBackoffice = $request->is('backoffice') || $request->is('backoffice/*') || $request->is('backportal') || $request->is('backportal/*');

            return \Inertia\Inertia::render('Errors/Error', [
                'status' => $status,
                'title' => $title,
                'message' => $friendlyMessage,
                'isBackoffice' => $isBackoffice,
            ])->toResponse($request)->setStatusCode($status);
        });
    })->create();
