<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelHasPermissionsTableSeeder extends Seeder
{
    public function run(): void
    {
        $demoUserIds = DB::table('users')
            ->whereIn('email', ['admin@demo.com', 'user@demo.com'])
            ->pluck('id')
            ->all();

        DB::table('model_has_permissions')
            ->where('model_type', 'App\\Models\\User')
            ->whereIn('model_id', $demoUserIds)
            ->delete();
    }
}
