<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);

        $this->call(MediaTableSeeder::class);

        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RoleHasPermissionsTableSeeder::class);
        $this->call(ModelHasRolesTableSeeder::class);
        $this->call(ModelHasPermissionsTableSeeder::class);

        $this->call(CategoriasTableSeeder::class);
        $this->call(SalasTableSeeder::class);
        $this->call(SalaCategoriasTableSeeder::class);
        $this->call(ImagenesTableSeeder::class);
        $this->call(ImagenCategoriaTableSeeder::class);
        $this->call(PartidasTableSeeder::class);
        $this->call(UsuarioPartidaTableSeeder::class);
        $this->call(PartidaImagenTableSeeder::class);
        $this->call(UsuarioSalaTableSeeder::class);

/*
 php artisan iseed media,model_has_permissions,model_has_roles,permissions,role_has_permissions,roles --exclude=created_at,updated_at --force

attempts, userts

*/

    }
}
