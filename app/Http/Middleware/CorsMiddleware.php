<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: Habilitar CORS para el frontend Astro.
 *
 * Permite peticiones desde el servidor de desarrollo de Astro (puerto 4321)
 * y cualquier origen en desarrollo local.
 */
class CorsMiddleware
{
    /**
     * Manejar la solicitud entrante.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');

        $contentType = $response->headers->get('Content-Type');
        if ($contentType && str_contains($contentType, 'application/json') && !str_contains(strtolower($contentType), 'charset=')) {
            $response->headers->set('Content-Type', $contentType.'; charset=UTF-8');
        }

        return $response;
    }
}
