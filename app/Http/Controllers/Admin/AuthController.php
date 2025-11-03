<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'sometimes|string|required_without:email',
            'email' => 'sometimes|email|required_without:phone',
            'password' => 'required|string',
        ]);

        // Normalize inputs
        $phone = isset($validated['phone']) ? trim($validated['phone']) : null;
        $email = isset($validated['email']) ? trim($validated['email']) : null;

        $user = null;
        if (!empty($phone)) {
            $user = User::where('phone', $phone)->first();
        } elseif (!empty($email)) {
            $user = User::where('email', $email)->first();
        }

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->hasRole('admin')) {
            return response()->json([
                'message' => 'Forbidden: admin access required',
            ], 403);
        }

        $token = $user->createToken('admin-auth-token')->plainTextToken;

        return response()->json([
            // Top-level keys for admin portal compatibility
            'token' => $token,
            'admin' => new UserResource($user),
            // Nested structure for API consistency
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data' => new UserResource($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}


