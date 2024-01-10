<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:users',
                'email' => 'required|unique:users',
                'password' => 'required|min:6',
                // Tambahkan aturan validasi lainnya sesuai kebutuhan
            ]);
        } catch (ValidationException $e) {
            return response([
                'errors' => $e->errors(),
            ], 400);
        }

        $user = new User([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            // Tambahkan atribut lainnya sesuai kebutuhan
        ]);

        $user->save();

        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email', // Email must be a string, a valid email and it is required
            'password' => 'required|string', // Password must be a string and it is required
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = $request->user();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request)
    {
        // Delete all tokens for the authenticated user
        $request->user()->tokens()->delete();

        // Return success message as JSON
        return response()->json(['message' => 'Logged out']);
    }
}
