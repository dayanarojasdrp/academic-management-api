<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\ApiQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->with('roles:id,code,name')
            ->orderBy('name')
            ->orderBy('id');

        ApiQuery::applyLike($query, $request, 'search', ['name', 'email']);
        ApiQuery::applyEquals($query, $request, ['status' => 'status']);

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($query) => $query->where('code', $request->query('role')));
        }

        return response()->json(ApiQuery::paginate($query, $request));
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->load('roles.permissions', 'student', 'professor'),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'status' => ['sometimes', 'string', 'max:30'],
            'student_id' => ['nullable', 'exists:students,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['required_with:roles', 'exists:roles,code'],
        ]);

        $user->fill(collect($validated)->except('roles')->all());
        $user->save();

        if (array_key_exists('roles', $validated)) {
            $roleIds = Role::query()->whereIn('code', $validated['roles'])->pluck('id');
            $user->roles()->sync($roleIds);
        }

        return response()->json([
            'user' => $user->fresh()->load('roles.permissions', 'student', 'professor'),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->update(['status' => 'inactive']);
        $user->tokens()->delete();

        return response()->json(status: 204);
    }
}
