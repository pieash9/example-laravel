<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['message' => 'user created'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('api')->user();

        $refreshToken = Str::random(64);

        DB::table('refresh_tokens')->insert([
            'user_id' => $user->id,
            'token' => $refreshToken,
            'expires_at' => now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()
            ->json(['message' => 'Login successful'])
            ->cookie('access_token', $token, 60, null, null, false, true)
            ->cookie('refresh_token', $refreshToken, 10080, null, null, false, true);
    }

    public function logout(Request $request)
    {
        $accessToken = $request->cookie('access_token');

        if (! $accessToken) {
            return response()->json(['error' => 'Access token not found'], 401);
        }

        try {
            Auth::guard('api')->setToken($accessToken)->logout();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $refreshToken = $request->cookie('refresh_token');
        if ($refreshToken) {
            DB::table('refresh_tokens')->where('token', $refreshToken)->delete();
        }

        return response()
            ->json(['message' => 'Successfully logged out'])
            ->cookie('access_token', '', -1)
            ->cookie('refresh_token', '', -1);
    }

    public function refresh(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (! $refreshToken) {
            return response()->json(['error' => 'Refresh token not found'], 401);
        }

        $token = DB::table('refresh_tokens')
            ->where('token', $refreshToken)
            ->where('expires_at', '>', now())
            ->first();

        if (! $token) {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }

        $user = User::find($token->user_id);

        $newAccessToken = Auth::guard('api')->login($user);

        return response()
            ->json(['message' => 'Token refreshed successfully'])
            ->cookie('access_token', $newAccessToken, 60, null, null, false, true);
    }
}
