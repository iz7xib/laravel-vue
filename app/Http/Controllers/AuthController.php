<?php

namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
           'name' => 'required|string',
           'email' => 'required|email|string|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ]
        ]);
        /** @var \App\Models\Users $users */
        $users = Users::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);
        $token = $users->createToken('main')->plainTextToken;

        return response([
            'user' => $users,
            'token' => $token
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|string|exists:users,email',
            'password' => [
                'required',
            ],
            'remember' => 'boolean'
        ]);
        $remember = $credentials['remember'] ?? false;
        unset($credentials['remember']);

        if (!Auth::attempt($credentials, $remember)) {
            return response([
                'error' => 'The Provided credentials are not correct'
            ], 422);
        }
        $users = Auth::user();
        $token = $users->createToken('main')->plainTextToken;

        return response([
            'user' => $users,
            'token' => $token
        ]);
    }

    public function logout()
    {
        /** @var Users $users */
        $users = Auth::user();
        // Revoke the token that was used to authenticate the current request...
        $users->currentAccessToken()->delete();

        return response([
            'success' => true
        ]);
    }
}
