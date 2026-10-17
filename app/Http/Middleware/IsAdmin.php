<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null) {
            // Гостю показываем витрину, где вход сделан модальным окном
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Необходима авторизация'], 401)
                : redirect()->route('products.index');
        }

        if (! $user->is_admin) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Доступ запрещён'], 403)
                : abort(403);
        }

        return $next($request);
    }
}
