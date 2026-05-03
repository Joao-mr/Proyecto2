<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            RoleHasPermissionsTableSeeder::class,
            ModelHasRolesTableSeeder::class,
            ModelHasPermissionsTableSeeder::class,
            CategoriasTableSeeder::class,
            GameImageCatalogSeeder::class,
        ]);
    }
}
