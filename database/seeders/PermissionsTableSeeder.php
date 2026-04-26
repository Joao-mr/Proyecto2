<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    public function run()
    {
        \DB::table('permissions')->delete();

        \DB::table('permissions')->insert([
            ['id' => 1, 'name' => 'role-list', 'guard_name' => 'web'],
            ['id' => 2, 'name' => 'role-create', 'guard_name' => 'web'],
            ['id' => 3, 'name' => 'role-edit', 'guard_name' => 'web'],
            ['id' => 4, 'name' => 'role-delete', 'guard_name' => 'web'],
            ['id' => 5, 'name' => 'permission-list', 'guard_name' => 'web'],
            ['id' => 6, 'name' => 'permission-create', 'guard_name' => 'web'],
            ['id' => 7, 'name' => 'permission-edit', 'guard_name' => 'web'],
            ['id' => 8, 'name' => 'permission-delete', 'guard_name' => 'web'],
            ['id' => 9, 'name' => 'user-list', 'guard_name' => 'web'],
            ['id' => 10, 'name' => 'user-create', 'guard_name' => 'web'],
            ['id' => 11, 'name' => 'user-edit', 'guard_name' => 'web'],
            ['id' => 12, 'name' => 'user-delete', 'guard_name' => 'web'],
        ]);
    }
}
