<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\AdminPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class AdminRoleController extends Controller
{
    public function index(Request $request): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        $roles = AdminRole::withCount(['users', 'permissions'])
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        $stats = [
            'total_roles' => AdminRole::count(),
            'system_roles' => AdminRole::where('is_system', true)->count(),
            'custom_roles' => AdminRole::where('is_system', false)->count(),
            'total_permissions' => AdminPermission::count(),
            'staff_users' => User::whereNotNull('admin_role_id')->count(),
        ];

        $adminRoles = AdminRole::where('is_active', true)->orderBy('name')->get();

        return view('admin.roles.index', compact('roles', 'stats', 'user', 'adminRoles'));
    }

    public function create(): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        $permissionsByModule = AdminPermission::getByModule();
        $moduleLabels = AdminPermission::getModuleLabels();

        return view('admin.roles.create', compact('user', 'permissionsByModule', 'moduleLabels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:admin_roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:admin_permissions,id',
        ]);

        try {
            DB::beginTransaction();

            $role = AdminRole::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'is_system' => false,
                'is_active' => true,
            ]);

            if (!empty($validated['permissions'])) {
                $role->syncPermissions($validated['permissions']);
            }

            DB::commit();

            Log::info('Admin role created', [
                'role_id' => $role->id,
                'name' => $role->name,
                'created_by' => Auth::id(),
            ]);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role "' . $role->name . '" created successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admin role', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create role. Please try again.');
        }
    }

    public function show(AdminRole $role): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        $role->load(['permissions', 'users']);
        $permissionsByModule = $role->permissions->groupBy('module');

        return view('admin.roles.show', compact('role', 'user', 'permissionsByModule'));
    }

    public function edit(AdminRole $role): View
    {
        $this->checkAdminAccess();
        $user = Auth::user();

        $permissionsByModule = AdminPermission::getByModule();
        $moduleLabels = AdminPermission::getModuleLabels();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'user', 'permissionsByModule', 'moduleLabels', 'rolePermissionIds'));
    }

    public function update(Request $request, AdminRole $role): RedirectResponse
    {
        $this->checkAdminAccess();

        if ($role->is_system && $role->slug === 'super-admin') {
            return redirect()->back()
                ->with('error', 'Cannot modify the Super Admin role.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:admin_roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:admin_permissions,id',
        ]);

        try {
            DB::beginTransaction();

            if (!$role->is_system) {
                $role->update([
                    'name' => $validated['name'],
                    'slug' => Str::slug($validated['name']),
                    'description' => $validated['description'] ?? null,
                ]);
            } else {
                $role->update([
                    'description' => $validated['description'] ?? null,
                ]);
            }

            $role->syncPermissions($validated['permissions'] ?? []);

            DB::commit();

            Log::info('Admin role updated', [
                'role_id' => $role->id,
                'name' => $role->name,
                'updated_by' => Auth::id(),
            ]);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role "' . $role->name . '" updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update admin role', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update role. Please try again.');
        }
    }

    public function destroy(AdminRole $role): RedirectResponse
    {
        $this->checkAdminAccess();

        if ($role->is_system) {
            return redirect()->back()
                ->with('error', 'Cannot delete system roles.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete role. There are ' . $role->users()->count() . ' users assigned to this role.');
        }

        try {
            $roleName = $role->name;
            $role->delete();

            Log::info('Admin role deleted', [
                'role_name' => $roleName,
                'deleted_by' => Auth::id(),
            ]);

            return redirect()->route('admin.roles.index')
                ->with('success', 'Role "' . $roleName . '" deleted successfully.');

        } catch (Exception $e) {
            Log::error('Failed to delete admin role', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to delete role. Please try again.');
        }
    }

    public function assignRole(Request $request): JsonResponse
    {
        $this->checkAdminAccess();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'nullable|exists:admin_roles,id',
        ]);

        try {
            $targetUser = User::findOrFail($validated['user_id']);
            
            if (!Auth::user()->isAdmin()) {
                return response()->json(['success' => false, 'message' => 'Only admins can assign roles.'], 403);
            }

            $targetUser->admin_role_id = $validated['role_id'];
            $targetUser->save();

            $roleName = $validated['role_id'] ? AdminRole::find($validated['role_id'])->name : 'None';

            Log::info('Admin role assigned to user', [
                'user_id' => $targetUser->id,
                'role_id' => $validated['role_id'],
                'assigned_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role "' . $roleName . '" assigned successfully.',
            ]);

        } catch (Exception $e) {
            Log::error('Failed to assign admin role', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to assign role.'], 500);
        }
    }

    private function checkAdminAccess(): void
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Access denied.');
        }
    }
}
