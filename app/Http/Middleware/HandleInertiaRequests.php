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
        $user = $request->user();
        
        $canAccess = [];
        $isAdmin = false;
        
        if ($user) {
            $isAdmin = $user->isAdmin();
            
            if ($isAdmin) {
                // Admin เข้าถึงได้ทุกอย่าง
                $canAccess = [
                    'sources' => true,
                    'results' => true,
                    'numbers' => true,
                    'lottoData' => true,
                ];
            } else {
                // Staff เช็คจาก permissions
                $permissions = $user->permissions()->pluck('slug')->toArray();
                
                $canAccess = [
                    'sources' => $user->canAccessRoute('backoffice.sources.index'),
                    'results' => $user->canAccessRoute('backoffice.results.index'),
                    'numbers' => $user->canAccessRoute('backoffice.numbers.index'),
                    'lottoData' => $user->canAccessRoute('backoffice.lotto-data.index'),
                ];
            }
        }
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_admin' => $isAdmin,
                    'can_access' => $canAccess,
                ] : null,
            ],
        ];
    }
}
