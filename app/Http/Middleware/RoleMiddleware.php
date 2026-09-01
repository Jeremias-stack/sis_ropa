<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Verificar si el usuario posee uno de los roles permitidos.
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {

        /*
         * Si no existe sesión iniciada,
         * Laravel debe enviarlo al login.
         */
        if (!auth()->check()) {

            return redirect()
                ->route('login');

        }


        /*
         * Rol actual del usuario.
         */
        $rolUsuario =
            auth()->user()->rol;


        /*
         * Verificar si tiene autorización.
         */
        if (!in_array(
            $rolUsuario,
            $roles,
            true
        )) {

            abort(
                403,
                'No tienes permisos para acceder a esta sección.'
            );

        }


        return $next($request);
    }
}