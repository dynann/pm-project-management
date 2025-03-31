<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // (validate the field after user enter)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // (table name user and add data to that field name table User)
        $user = User::create([
            'username' => $request->name, 
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'roleSystem' => 'developer', // Add this line with an appropriate default role
        ]);

        // create access token
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(60))->plainTextToken;
        
        // create refresh token with longer expiration
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30))->plainTextToken;

        // return response with both tokens
        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ], 201)->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Refresh-Token' => $refreshToken
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        
        // tevoke previous tokens for security (optional, you can remove this if you want multiple logins)
        $user->tokens()->delete();
        
        // create access token with shorter expiration
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(60))->plainTextToken;
        
        // create refresh token with longer expiration
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30))->plainTextToken;

        // return response with both tokens
        return response()->json([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Refresh-Token' => $refreshToken
        ]);
    }

    public function refreshToken(Request $request)
    {
        // user is already authenticated with refresh token at this point
        $user = $request->user();
        
        // create new access token
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(60))->plainTextToken;
        
        // create new refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30))->plainTextToken;
        
        // revoke the current token (optional)
        $request->user()->currentAccessToken()->delete();
    
        // return new tokens
        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ])->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'X-Refresh-Token' => $refreshToken
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            if (!$request->user()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            return response()->json([
                'user' => $request->user()->only(['id', 'username', 'email', 'roleSystem'])
            ]);
        } catch (\Exception $e) {
            Log::error('User fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch user data'], 500);
        }
    }
}