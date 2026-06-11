<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/admin/users
     * Admin only — list all users.
     */
    public function index(Request $request): JsonResponse
    {
        // Only admin can access
        if (! $request->user()->admin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $query = User::query()->select([
            'user_id', 'first_name', 'last_name', 'email_address',
            'mobile_number', 'status', 'admin', 'module', 'type',
            'date_created', 'date_modified', 'last_login',
        ]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email_address', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $sortCol = $request->get('sort', 'date_created');
        $sortDir = $request->get('dir', 'desc');
        $allowed = ['user_id', 'first_name', 'last_name', 'email_address', 'status', 'type', 'date_created', 'last_login'];
        if (in_array($sortCol, $allowed)) {
            $query->orderBy($sortCol, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->get('per_page', 25), 100);
        $results = $query->paginate($perPage);

        return response()->json([
            'data' => $results->items(),
            'meta' => [
                'current_page' => $results->currentPage(),
                'per_page'     => $results->perPage(),
                'total'        => $results->total(),
                'last_page'    => $results->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/admin/users
     */
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->admin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'email_address'  => ['required', 'email', 'unique:el_user,email_address'],
            'mobile_number'  => ['nullable', 'string', 'max:50'],
            'password'       => ['required', 'string', 'min:6'],
            'status'         => ['nullable', 'string', 'in:active,inactive'],
            'admin'          => ['nullable', 'integer', 'in:0,1'],
            'module'         => ['nullable', 'integer'],
            'type'           => ['nullable', 'integer'],
        ]);

        $validated['password']     = Hash::make($validated['password']);
        $validated['status']       = $validated['status'] ?? 'active';
        $validated['admin']        = $validated['admin'] ?? 0;
        $validated['date_created'] = now();

        $user = User::create($validated);

        return response()->json([
            'data' => $user->only(['user_id', 'first_name', 'last_name', 'email_address', 'status', 'admin', 'module', 'type']),
        ], 201);
    }

    /**
     * PUT /api/admin/users/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->admin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'first_name'    => ['sometimes', 'string', 'max:255'],
            'last_name'     => ['sometimes', 'string', 'max:255'],
            'email_address' => ['sometimes', 'email', Rule::unique('el_user', 'email_address')->ignore($id, 'user_id')],
            'mobile_number' => ['nullable', 'string', 'max:50'],
            'status'        => ['nullable', 'string', 'in:active,inactive'],
            'admin'         => ['nullable', 'integer', 'in:0,1'],
            'module'        => ['nullable', 'integer'],
            'type'          => ['nullable', 'integer'],
        ]);

        $validated['date_modified'] = now();
        $user->update($validated);

        return response()->json([
            'data' => $user->fresh()->only(['user_id', 'first_name', 'last_name', 'email_address', 'status', 'admin', 'module', 'type', 'date_modified']),
        ]);
    }

    /**
     * POST /api/admin/users/{id}/reset-password
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->admin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update([
            'password'      => Hash::make($request->password),
            'date_modified' => now(),
        ]);

        return response()->json(['message' => "Password untuk {$user->full_name} berhasil direset."]);
    }

    /**
     * DELETE /api/admin/users/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if (! $request->user()->admin) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Protect: cannot delete yourself
        if ($request->user()->user_id === $id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => "User '{$user->full_name}' berhasil dihapus."]);
    }
}
