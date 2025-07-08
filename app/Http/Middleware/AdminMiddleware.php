<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        // Vérifier si l'utilisateur est admin (role 2)
        if (auth()->user()->id_role !== 2) {
            return response()->json(['message' => 'Accès refusé - Admin requis'], 403);
        }

        return $next($request);
    }
}