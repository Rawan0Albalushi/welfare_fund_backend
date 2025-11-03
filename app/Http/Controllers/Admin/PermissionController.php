<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Permission::query();

        if ($search = trim((string) $request->get('search'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($group = trim((string) $request->get('group'))) {
            // Group permissions by prefix (e.g., view_, create_, edit_, delete_)
            $query->where('name', 'like', "{$group}%");
        }

        $permissions = $query->orderBy('name', 'asc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'message' => 'Permissions retrieved successfully',
            'data' => PermissionResource::collection($permissions),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
                'last_page' => $permissions->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'guard_name' => 'sometimes|string|max:255',
        ]);

        $guardName = $validated['guard_name'] ?? config('auth.defaults.guard');

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $guardName,
        ]);

        return response()->json([
            'message' => 'Permission created successfully',
            'data' => new PermissionResource($permission),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        return response()->json([
            'message' => 'Permission retrieved successfully',
            'data' => new PermissionResource($permission),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:permissions,name,' . $id,
            'guard_name' => 'sometimes|string|max:255',
        ]);

        if (isset($validated['name'])) {
            $permission->name = $validated['name'];
        }

        if (isset($validated['guard_name'])) {
            $permission->guard_name = $validated['guard_name'];
        }

        $permission->save();

        return response()->json([
            'message' => 'Permission updated successfully',
            'data' => new PermissionResource($permission),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully',
        ]);
    }
}

