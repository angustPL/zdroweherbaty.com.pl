<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetOrderNotFoundStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Sprawdź czy w sesji jest flaga order_not_found_404
        if (session('order_not_found_404')) {
            // Ustaw kod odpowiedzi na 404
            $response->setStatusCode(404);
            // Usuń flagę z sesji
            session()->forget('order_not_found_404');
        }

        return $response;
    }
}
