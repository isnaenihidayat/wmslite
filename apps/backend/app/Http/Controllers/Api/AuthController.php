<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * Returns a Sanctum Bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email_address', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive. Please contact admin.',
            ], 403);
        }

        // Revoke old tokens and create fresh one
        $user->tokens()->delete();

        $token = $user->createToken('wms-lite-token')->plainTextToken;

        // Update last_login
        $user->last_login = now();
        $user->save();

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * Format user for consistent API response.
     */
    private function formatUser(User $user): array
    {
        return [
            'id'         => $user->user_id,
            'name'       => $user->full_name,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email_address,
            'mobile'     => $user->mobile_number,
            'status'     => $user->status,
            'is_admin'   => (bool) $user->admin,
            'module'     => $user->module,
            'type'       => $user->type,
            'last_login' => $user->last_login,
        ];
    }
}
