<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')
            ->where('guard_name', 'web')
            ->where('name', 'user')
            ->delete();

        $roles = [
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'player', 'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                []
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
