<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        if($user==null){
            return Response()->json(['success'=>false,'message'=>'You are not authorized to access this page']);
        }
        elseif($user->is_admin===1){
            return $next($request);
        }
        else{
            return Response()->json(['success'=>false,'message'=>'Permission denied']);
        }
    }
}
