<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Console\Command;

class ManagePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:manage {action} {--role=} {--permission=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quản lý quyền và vai trò trong hệ thống';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'list-roles':
                $this->listRoles();
                break;
            case 'list-permissions':
                $this->listPermissions();
                break;
            case 'assign':
                $this->assignPermission();
                break;
            case 'revoke':
                $this->revokePermission();
                break;
            case 'create-role':
                $this->createRole();
                break;
            case 'create-permission':
                $this->createPermission();
                break;
            default:
                $this->error("Hành động không hợp lệ. Các hành động hợp lệ: list-roles, list-permissions, assign, revoke, create-role, create-permission");
        }
    }

    /**
     * Liệt kê tất cả vai trò.
     */
    private function listRoles()
    {
        $roles = Role::withCount('permissions')->get();
        
        $this->table(
            ['ID', 'Tên', 'Slug', 'Số lượng quyền'],
            $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'permissions_count' => $role->permissions_count,
                ];
            })
        );
    }

    /**
     * Liệt kê tất cả quyền.
     */
    private function listPermissions()
    {
        $permissions = Permission::withCount('roles')->get();
        
        $this->table(
            ['ID', 'Tên', 'Slug', 'Model', 'Số lượng vai trò'],
            $permissions->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'slug' => $permission->slug,
                    'model' => $permission->model ?? 'N/A',
                    'roles_count' => $permission->roles_count,
                ];
            })
        );
    }

    /**
     * Gán quyền cho vai trò.
     */
    private function assignPermission()
    {
        $roleSlug = $this->option('role');
        $permissionSlug = $this->option('permission');
        
        if (!$roleSlug || !$permissionSlug) {
            $this->error('Cần cung cấp cả --role và --permission');
            return;
        }
        
        $role = Role::where('slug', $roleSlug)->first();
        if (!$role) {
            $this->error("Không tìm thấy vai trò với slug: {$roleSlug}");
            return;
        }
        
        $permission = Permission::where('slug', $permissionSlug)->first();
        if (!$permission) {
            $this->error("Không tìm thấy quyền với slug: {$permissionSlug}");
            return;
        }
        
        $role->givePermissionTo($permission);
        $this->info("Đã gán quyền '{$permission->name}' cho vai trò '{$role->name}'");
    }

    /**
     * Thu hồi quyền từ vai trò.
     */
    private function revokePermission()
    {
        $roleSlug = $this->option('role');
        $permissionSlug = $this->option('permission');
        
        if (!$roleSlug || !$permissionSlug) {
            $this->error('Cần cung cấp cả --role và --permission');
            return;
        }
        
        $role = Role::where('slug', $roleSlug)->first();
        if (!$role) {
            $this->error("Không tìm thấy vai trò với slug: {$roleSlug}");
            return;
        }
        
        $permission = Permission::where('slug', $permissionSlug)->first();
        if (!$permission) {
            $this->error("Không tìm thấy quyền với slug: {$permissionSlug}");
            return;
        }
        
        $role->revokePermissionTo($permission);
        $this->info("Đã thu hồi quyền '{$permission->name}' từ vai trò '{$role->name}'");
    }

    /**
     * Tạo vai trò mới.
     */
    private function createRole()
    {
        $name = $this->ask('Nhập tên vai trò');
        $slug = $this->ask('Nhập slug vai trò (chỉ chứa chữ thường, số và dấu gạch ngang)');
        
        if (Role::where('slug', $slug)->exists()) {
            $this->error("Vai trò với slug '{$slug}' đã tồn tại");
            return;
        }
        
        $role = Role::create([
            'name' => $name,
            'slug' => $slug,
        ]);
        
        $this->info("Đã tạo vai trò '{$name}' với ID: {$role->id}");
    }

    /**
     * Tạo quyền mới.
     */
    private function createPermission()
    {
        $name = $this->ask('Nhập tên quyền');
        $slug = $this->ask('Nhập slug quyền (chỉ chứa chữ thường, số và dấu gạch ngang)');
        $model = $this->ask('Nhập model liên quan (để trống nếu không có)');
        $description = $this->ask('Nhập mô tả quyền (để trống nếu không có)');
        
        if (Permission::where('slug', $slug)->exists()) {
            $this->error("Quyền với slug '{$slug}' đã tồn tại");
            return;
        }
        
        $permission = Permission::create([
            'name' => $name,
            'slug' => $slug,
            'model' => $model ?: null,
            'description' => $description ?: null,
        ]);
        
        $this->info("Đã tạo quyền '{$name}' với ID: {$permission->id}");
    }
} 