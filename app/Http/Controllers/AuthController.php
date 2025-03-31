<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:password',
            'gender' => 'nullable|string|in:male,female,other',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'roleSystem' => 'user',
        ]);

        $accessToken = JWTAuth::fromUser($user, ['exp' => now()->addMinutes(15)->timestamp]);
        $refreshToken = JWTAuth::fromUser($user, ['exp' => now()->addDays(30)->timestamp, 'type' => 'refresh']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        try {
            if (!$accessToken = JWTAuth::attempt($credentials, ['exp' => now()->addMinutes(15)->timestamp])) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Could not create token'], 500);
        }

        $user = auth()->user();
        $refreshToken = JWTAuth::fromUser($user, ['exp' => now()->addDays(30)->timestamp, 'type' => 'refresh']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->bearerToken();

        if (!$refreshToken) {
            return response()->json(['message' => 'No token provided'], 401);
        }

        try {
            $payload = JWTAuth::setToken($refreshToken)->getPayload();
            if ($payload['type'] !== 'refresh') {
                return response()->json(['message' => 'Invalid refresh token'], 401);
            }

            $user = JWTAuth::setToken($refreshToken)->toUser();
            $newAccessToken = JWTAuth::fromUser($user, ['exp' => now()->addMinutes(15)->timestamp]);
            $newRefreshToken = JWTAuth::fromUser($user, ['exp' => now()->addDays(30)->timestamp, 'type' => 'refresh']);

            return response()->json([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken,
                'token_type' => 'Bearer',
            ]);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Logged out successfully']);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Failed to logout'], 500);
        }
    }

    public function getUserInfo(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json([
                'user' => [
                    'username' => $user->username,
                    'email' => $user->email,
                ]
            ]);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
    }
}