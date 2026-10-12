<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class Login extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'redirect' => '/products'
            ]);
        }
        return response()->json([
            'errors' => [
                'email' => ['Указана неверная почта или пароль']
            ]
        ], 422);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->intended('/products');
    }
}
