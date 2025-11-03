<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Role::query()->with('permissions');

        if ($search = trim((string) $request->get('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->orderBy('name', 'asc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'message' => 'Roles retrieved successfully',
            'data' => RoleResource::collection($roles),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
                'last_page' => $roles->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'sometimes|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $guardName = $validated['guard_name'] ?? config('auth.defaults.guard');
        
        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => $guardName,
        ]);

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role created successfully',
            'data' => new RoleResource($role),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'message' => 'Role retrieved successfully',
            'data' => new RoleResource($role),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $id,
            'guard_name' => 'sometimes|string|max:255',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if (isset($validated['name'])) {
            $role->name = $validated['name'];
        }

        if (isset($validated['guard_name'])) {
            $role->guard_name = $validated['guard_name'];
        }

        $role->save();

        if (isset($validated['permissions'])) {
            $permissions = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => new RoleResource($role),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // Prevent deletion of default roles
        $defaultRoles = ['admin', 'reviewer', 'user'];
        if (in_array($role->name, $defaultRoles)) {
            return response()->json([
                'message' => 'Cannot delete default roles',
                'error' => 'Default roles (admin, reviewer, user) cannot be deleted',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully',
        ]);
    }
}

