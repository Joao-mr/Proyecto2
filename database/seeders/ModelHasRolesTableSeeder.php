<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ModelHasRolesTableSeeder extends Seeder
{
    public function run(): void
    {
        $usersByEmail = DB::table('users')->pluck('id', 'email')->all();
        $rolesByName = DB::table('roles')->pluck('id', 'name')->all();

        $assignments = [
            ['email' => 'admin@demo.com', 'role' => 'admin'],
            ['email' => 'user@demo.com', 'role' => 'player'],
        ];

        foreach ($assignments as $assignment) {
            $modelId = $usersByEmail[$assignment['email']] ?? null;
            $roleId = $rolesByName[$assignment['role']] ?? null;

            if (! is_int($modelId) || ! is_int($roleId)) {
                throw new RuntimeException('ModelHasRolesTableSeeder: user or role not found for assignment.');
            }

            DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->where('model_id', $modelId)
                ->delete();

            DB::table('model_has_roles')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $modelId,
                ],
                []
            );
        }
    }
}
