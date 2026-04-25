<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:vendors',
            'password' => 'required|string|min:8|confirmed',
            'store_name' => 'required|string|max:255',
            'store_description' => 'nullable|string',
        ]);

        $vendor = Vendor::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'store_name' => $validated['store_name'],
            'store_description' => $validated['store_description'] ?? null,
        ]);

        $token = $vendor->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Vendor registered successfully',
            'data' => [
                'vendor' => $vendor,
                'token' => $token,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $vendor = Vendor::where('email', $validated['email'])->first();

        if (!$vendor || !Hash::check($validated['password'], $vendor->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $vendor->tokens()->delete();
        $token = $vendor->createToken('vendor-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Logged in successfully',
            'data' => [
                'vendor' => $vendor,
                'token' => $token,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}