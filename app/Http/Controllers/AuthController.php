<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'result' => false,
                'message' => 'Invaid Credentials!'
            ], 401);
        }
        $user = $request->user();
        $tokenResult = $user->createToken($request->input('email'));
        $token = $tokenResult->plainTextToken;
        return response()->json([
            'result' => true,
            'token' => $token,
            'user' => Auth::user()
        ], 200);
    }
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users',
            'password' => 'required|string',
            'c_password' => 'required|same:password'
        ]);
        $user = new User([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($user->save()) {
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'message' => 'Successfully created user!',
                'accessToken' => $token,
            ], 201);
        } else {
            return response()->json(['error' => 'Provide proper details']);
        }
    }
}
