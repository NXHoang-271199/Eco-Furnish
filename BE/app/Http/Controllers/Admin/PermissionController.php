<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.only');
    }

    /**
     * Hiển thị trang quản lý quyền.
     */
    public function index()
    {
        $roles = Role::withCount('permissions')->get();
        $permissions = Permission::withCount('roles')->get();
        
        return view('admins.permissions.index', compact('roles', 'permissions'));
    }

    /**
     * Hiển thị trang chi tiết vai trò.
     */
    public function showRole(Role $role)
    {
        $role->load('permissions');
        $allPermissions = Permission::all();
        
        return view('admins.permissions.role', compact('role', 'allPermissions'));
    }

    /**
     * Cập nhật quyền cho vai trò.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Xóa tất cả quyền hiện tại
            $role->permissions()->detach();
            
            // Gán quyền mới
            if (isset($validated['permissions'])) {
                $role->permissions()->attach($validated['permissions']);
            }
            
            DB::commit();
            
            return redirect()->route('admin.permissions.role', $role)
                ->with('success', 'Quyền của vai trò đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi khi cập nhật quyền: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form tạo vai trò mới.
     */
    public function createRole()
    {
        return view('admins.permissions.create-role');
    }

    /**
     * Lưu vai trò mới.
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug',
        ]);
        
        $role = Role::create($validated);
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Vai trò đã được tạo thành công.');
    }

    /**
     * Hiển thị form tạo quyền mới.
     */
    public function createPermission()
    {
        return view('admins.permissions.create-permission');
    }

    /**
     * Lưu quyền mới.
     */
    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:permissions,slug',
            'model' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $permission = Permission::create($validated);
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Quyền đã được tạo thành công.');
    }

    /**
     * Xóa vai trò.
     */
    public function destroyRole(Role $role)
    {
        // Không cho phép xóa vai trò admin
        if ($role->slug === 'admin') {
            return redirect()->back()
                ->with('error', 'Không thể xóa vai trò Admin.');
        }
        
        $role->delete();
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Vai trò đã được xóa thành công.');
    }

    /**
     * Xóa quyền.
     */
    public function destroyPermission(Permission $permission)
    {
        // Thu hồi quyền này từ tất cả vai trò
        $permission->roles()->detach();
        
        $permission->delete();
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Quyền đã được xóa thành công.');
    }
} 