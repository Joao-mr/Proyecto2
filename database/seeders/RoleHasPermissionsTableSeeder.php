<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RoleHasPermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')
            ->where('name', 'admin')
            ->where('guard_name', 'web')
            ->value('id');

        if (! is_int($adminRoleId)) {
            throw new RuntimeException('RoleHasPermissionsTableSeeder: missing admin role.');
        }

        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->pluck('id', 'name')
            ->all();

        $permissionNames = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'admin-stats-reset',
        ];

        foreach ($permissionNames as $permissionName) {
            $permissionId = $permissionIds[$permissionName] ?? null;

            if (! is_int($permissionId)) {
                throw new RuntimeException("RoleHasPermissionsTableSeeder: missing permission {$permissionName}.");
            }

            DB::table('role_has_permissions')->updateOrInsert(
                ['role_id' => $adminRoleId, 'permission_id' => $permissionId],
                []
            );
        }
    }
}
