<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApkUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ApkController extends Controller
{
    /**
     * GET /api/master/apk
     * List APK scanner accounts from both el_apk and el_apk_s.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $table  = $request->get('table', 'all'); // 'all' | 'main' | 'staging'
        $perPage = min((int) $request->get('per_page', 25), 100);
        $sortCol = in_array($request->get('sort'), ['id', 'name', 'username', 'last_login']) ? $request->get('sort') : 'id';
        $sortDir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $buildQuery = function (string $tbl, string $label) use ($search, $sortCol, $sortDir) {
            $q = DB::table($tbl)
                ->select(
                    DB::raw("id"),
                    DB::raw("name"),
                    DB::raw("username"),
                    DB::raw("last_login"),
                    DB::raw("CASE WHEN token IS NOT NULL AND token != '' THEN 1 ELSE 0 END as is_logged_in"),
                    DB::raw("'{$label}' as source_table")
                );

            if ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            }

            return $q;
        };

        if ($table === 'main') {
            $query = $buildQuery('el_apk', 'main');
        } elseif ($table === 'staging') {
            $query = $buildQuery('el_apk_s', 'staging');
        } else {
            // Union both tables
            $query = $buildQuery('el_apk', 'main')
                ->union($buildQuery('el_apk_s', 'staging'));
        }

        $query->orderBy($sortCol, $sortDir);

        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/master/apk
     * Create new APK account in el_apk.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('store', ApkUser::class);

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'username'   => ['required', 'string', 'max:255', 'unique:el_apk,username'],
            'password'   => ['required', 'string', 'min:4'],
            'table'      => ['nullable', 'string', 'in:main,staging'],
        ]);

        $tbl  = ($validated['table'] ?? 'main') === 'staging' ? 'el_apk_s' : 'el_apk';
        $pass = Hash::make($validated['password']);

        // Validate uniqueness against chosen table
        if ($tbl === 'el_apk_s') {
            $request->validate(['username' => Rule::unique('el_apk_s', 'username')]);
        }

        $id = DB::table($tbl)->insertGetId([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'password' => $pass,
        ]);

        $record = DB::table($tbl)->where('id', $id)->first();

        return response()->json([
            'data' => [
                'id'           => $record->id,
                'name'         => $record->name,
                'username'     => $record->username,
                'last_login'   => $record->last_login,
                'is_logged_in' => false,
                'source_table' => $tbl === 'el_apk_s' ? 'staging' : 'main',
            ],
        ], 201);
    }

    /**
     * PUT /api/master/apk/{id}
     * Update APK account name/username.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $this->authorize('update', ApkUser::class);

        $tbl   = $request->get('table', 'main') === 'staging' ? 'el_apk_s' : 'el_apk';
        $record = DB::table($tbl)->where('id', $id)->first();

        if (! $record) {
            return response()->json(['message' => 'APK account not found.'], 404);
        }

        $validated = $request->validate([
            'name'     => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255',
                Rule::unique($tbl, 'username')->ignore($id),
            ],
        ]);

        DB::table($tbl)->where('id', $id)->update($validated);

        $updated = DB::table($tbl)->where('id', $id)->first();

        return response()->json([
            'data' => [
                'id'           => $updated->id,
                'name'         => $updated->name,
                'username'     => $updated->username,
                'last_login'   => $updated->last_login,
                'is_logged_in' => ! empty($updated->token),
                'source_table' => $tbl === 'el_apk_s' ? 'staging' : 'main',
            ],
        ]);
    }

    /**
     * POST /api/master/apk/{id}/reset-password
     */
    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $this->authorize('resetPassword', ApkUser::class);

        $tbl    = $request->get('table', 'main') === 'staging' ? 'el_apk_s' : 'el_apk';
        $record = DB::table($tbl)->where('id', $id)->first();

        if (! $record) {
            return response()->json(['message' => 'APK account not found.'], 404);
        }

        $request->validate(['password' => ['required', 'string', 'min:4']]);

        DB::table($tbl)->where('id', $id)->update([
            'password' => Hash::make($request->password),
            'token'    => null, // force re-login
        ]);

        return response()->json([
            'message' => "Password untuk '{$record->name}' berhasil direset.",
        ]);
    }

    /**
     * POST /api/master/apk/{id}/logout
     * Force logout by clearing token.
     */
    public function logout(Request $request, int $id): JsonResponse
    {
        $this->authorize('logout', ApkUser::class);

        $tbl    = $request->get('table', 'main') === 'staging' ? 'el_apk_s' : 'el_apk';
        $record = DB::table($tbl)->where('id', $id)->first();

        if (! $record) {
            return response()->json(['message' => 'APK account not found.'], 404);
        }

        DB::table($tbl)->where('id', $id)->update(['token' => null]);

        return response()->json([
            'message' => "Session '{$record->name}' berhasil di-logout.",
        ]);
    }

    /**
     * DELETE /api/master/apk/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->authorize('delete', ApkUser::class);

        $tbl    = $request->get('table', 'main') === 'staging' ? 'el_apk_s' : 'el_apk';
        $record = DB::table($tbl)->where('id', $id)->first();

        if (! $record) {
            return response()->json(['message' => 'APK account not found.'], 404);
        }

        DB::table($tbl)->where('id', $id)->delete();

        return response()->json([
            'message' => "Account '{$record->name}' berhasil dihapus.",
        ]);
    }
}
