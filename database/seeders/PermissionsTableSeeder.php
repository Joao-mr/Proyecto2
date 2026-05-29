<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class PermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'categoria-create',
                'categoria-edit',
                'categoria-delete',
                'sala-edit',
                'sala-delete',
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
            ])
            ->delete();

        $permissions = [
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

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                []
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
