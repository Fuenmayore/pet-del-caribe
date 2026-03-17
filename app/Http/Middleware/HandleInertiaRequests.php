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
        return [
            ...parent::share($request),
            'auth' => [
                // Modificamos esto para enviar roles y permisos
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'nombre' => $request->user()->nombre, // O 'name', según tu base de datos
                    'email' => $request->user()->email,
                    // AGREGAMOS ESTAS DOS LÍNEAS:
                    'roles' => $request->user()->getRoleNames(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            // ... tus otros datos compartidos como flash messages ...
        ];
    }
}
