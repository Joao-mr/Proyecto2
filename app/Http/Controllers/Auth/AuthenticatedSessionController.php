<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $request->authenticate();

        $user = $request->user()->loadMissing('roles.permissions');
        $token = $user->createToken($request->userAgent())->plainTextToken;

        if ($request->wantsJson()) {
            return response()->json([
                'user' => array_merge($user->toArray(), [
                    'roles' => $user->roles->values(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                ]),
                'token' => $token,
            ]);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect('/');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'name' => $request['name'],
            'surname1' => $request['surname1'],
            'surname2' => $request['surname2'],
        ]);

        $role = Role::where('name', 'player')->where('guard_name', 'web')->firstOrFail();
        $user->assignRole($role);

        return new \App\Http\Resources\UserResource($user);
    }
}
