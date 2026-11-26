<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Login สำหรับ admin/staff (/backoffice/login) และ เจ้าสำนัก (/backportal/login)
 */
class BackofficeAuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/BackofficeLogin', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user('web');

        // หากเป็นบัญชีเจ้าสำนัก (และไม่ใช่ Admin) ให้พาไปหน้า /backportal เสมอ ห้ามเข้า /backoffice/dashboard
        if ($user && $user->isSourceOwner() && !$user->isAdmin()) {
            return redirect()->route('backportal.index');
        }

        return redirect()->intended(route('backoffice.dashboard', absolute: false));
    }

    /**
     * หน้า Login เฉพาะสำหรับเจ้าสำนัก (/backportal/login)
     */
    public function createPortal(): Response
    {
        return Inertia::render('Auth/BackportalLogin', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * ประมวลผล Login จากหน้า /backportal/login
     */
    public function storePortal(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user('web');

        if (!$user || (!$user->isSourceOwner() && !$user->isAdmin())) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'บัญชีนี้ยังไม่ได้ผูกสิทธิ์เจ้าสำนัก กรุณาติดต่อแอดมิน',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('backportal.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
