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
            ->with(['roles:id,code,name', 'institution:id,code,name', 'campus:id,code,name'])
            ->orderBy('name')
            ->orderBy('id');

        ApiQuery::applyLike($query, $request, 'search', ['name', 'email']);
        ApiQuery::applyEquals($query, $request, [
            'status' => 'status',
            'institution_id' => 'institution_id',
            'campus_id' => 'campus_id',
        ]);

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($query) => $query->where('code', $request->query('role')));
        }

        return response()->json(ApiQuery::paginate($query, $request));
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->load('roles.permissions', 'student', 'professor', 'institution', 'campus'),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
            'status' => ['sometimes', 'string', 'max:30'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
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
            'user' => $user->fresh()->load('roles.permissions', 'student', 'professor', 'institution', 'campus'),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->update(['status' => 'inactive']);
        $user->tokens()->delete();

        return response()->json(status: 204);
    }
}
