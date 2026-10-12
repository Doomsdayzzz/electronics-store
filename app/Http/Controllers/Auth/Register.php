<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Register extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserStoreRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($request->password);
        $user = User::query()->create($validated);
        Auth::login($user);
        return redirect('/products');
    }
}
