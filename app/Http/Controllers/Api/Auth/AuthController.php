<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::query()->with('roles.permissions')->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son validas.',
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => 'El usuario no esta activo.',
            ]);
        }

        $permissions = $user->permissionCodes();
        $token = $user->createToken($credentials['device_name'] ?? 'api-client', $permissions);
        $user->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'user' => $this->userPayload($user->fresh()->load('roles.permissions')),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()->load('roles.permissions', 'student', 'professor')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Sesion cerrada correctamente.']);
    }

    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8'],
            'status' => ['nullable', 'string', 'max:30'],
            'student_id' => ['nullable', 'exists:students,id'],
            'professor_id' => ['nullable', 'exists:professors,id'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'exists:roles,code'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => $validated['status'] ?? 'active',
            'student_id' => $validated['student_id'] ?? null,
            'professor_id' => $validated['professor_id'] ?? null,
        ]);

        $roleIds = Role::query()->whereIn('code', $validated['roles'])->pluck('id');
        $user->roles()->sync($roleIds);

        return response()->json(['user' => $this->userPayload($user->load('roles.permissions'))], 201);
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'student_id' => $user->student_id,
            'professor_id' => $user->professor_id,
            'roles' => $user->roles->pluck('code')->values(),
            'permissions' => collect($user->permissionCodes())->values(),
            'last_login_at' => $user->last_login_at,
        ];
    }
}
