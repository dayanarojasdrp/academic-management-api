<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class AuthorizationController extends Controller
{
    public function roles(): JsonResponse
    {
        return response()->json([
            'roles' => Role::query()
                ->with('permissions:id,code,name,module')
                ->orderBy('code')
                ->get(),
        ]);
    }

    public function permissions(): JsonResponse
    {
        return response()->json([
            'permissions' => Permission::query()
                ->orderBy('module')
                ->orderBy('code')
                ->get()
                ->groupBy('module'),
        ]);
    }
}
