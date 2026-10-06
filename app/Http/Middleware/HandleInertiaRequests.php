<?php

namespace App\Http\Middleware;

use App\Models\Administrador;
use App\Models\Estudiante;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = Auth::user();
        $estudiante = null;
        $admin = null;

        if ($user && $user->rol === 'estudiante') {
            $estudiante = Estudiante::where('usuario', $user->id)->first();
        } elseif ($user && $user->isAdmin()) {
            $admin = Administrador::where('usuario_id', $user->id)->first();
        }

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'correo' => $user->correo,
                    'rol' => $user->rol,
                ] : null,
                'estudiante' => $estudiante ? [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->nombre,
                    'paterno' => $estudiante->paterno,
                    'materno' => $estudiante->materno,
                    'nombre_completo' => trim("{$estudiante->nombre} {$estudiante->paterno} {$estudiante->materno}"),
                    'plan_activo' => (bool) $estudiante->plan_activo,
                    'foto_url' => $estudiante->foto ? Storage::url($estudiante->foto) : null,
                ] : null,
                'admin' => $admin ? [
                    'id' => $admin->id,
                    'nombre' => $admin->nombre,
                    'apellido_paterno' => $admin->apellido_paterno,
                    'nombre_completo' => trim("{$admin->nombre} {$admin->apellido_paterno} {$admin->apellido_materno}"),
                ] : null,
            ],

            'notificaciones' => function () use ($user) {
                if (! $user) {
                    return ['no_leidas' => 0];
                }
                try {
                    return ['no_leidas' => Notificacion::where('id_usuario', $user->id)->whereNull('leida_at')->count()];
                } catch (\Throwable $e) {
                    return ['no_leidas' => 0];
                }
            },

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],

            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
