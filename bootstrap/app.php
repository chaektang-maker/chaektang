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

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // จัดการ 404 errors สำหรับ Inertia
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'ไม่พบหน้านี้'], 404);
            }
            
            // สำหรับ web requests ใช้ Inertia พร้อม header และ footer
            return \Inertia\Inertia::render('Errors/404', [
                'status' => 404,
                'canLogin' => \Illuminate\Support\Facades\Route::has('login'),
                'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
            ])->toResponse($request)->setStatusCode(404);
        });
    })->create();
