<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

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
            'categorias-crear',
            'categorias-editar',
            'categorias-eliminar',
            'salas-editar',
            'salas-eliminar',
            'roles-ver',
            'roles-crear',
            'roles-editar',
            'roles-eliminar',
            'permisos-ver',
            'permisos-crear',
            'permisos-editar',
            'permisos-eliminar',
            'usuarios-ver',
            'usuarios-crear',
            'usuarios-editar',
            'usuarios-eliminar',
            'jugadores-estadisticas-reiniciar',
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
