<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Sala;
use App\Models\User;
use Database\Seeders\FoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_json_for_unauthenticated_requests_without_accept_header(): void
    {
        $response = $this->get('/api/user/stats');

        $response->assertUnauthorized();
        $this->assertStringContainsString(
            'application/json',
            (string) $response->headers->get('Content-Type')
        );
    }

    public function test_user_can_view_own_user_resource_without_admin_permission(): void
    {
        $user = $this->createUser('self');

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/users/'.$user->id)
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_user_cannot_view_another_user_without_user_list_permission(): void
    {
        Permission::findOrCreate('usuarios-ver', 'web');
        $user = $this->createUser('viewer');
        $otherUser = $this->createUser('other');

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/users/'.$otherUser->id)->assertForbidden();
    }

    public function test_user_with_user_list_permission_can_list_users(): void
    {
        $user = $this->createUser('admin-viewer');
        $this->givePermission($user, 'usuarios-ver');

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_only_admin_role_can_create_categories(): void
    {
        $user = $this->createUser('category-blocked');
        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/categorias', [
            'nombre' => 'Categoria bloqueada',
        ])->assertForbidden();

        $admin = $this->createUser('category-admin');
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo(Permission::findOrCreate('categorias-crear', 'web'));
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum');

        $this->postJson('/api/categorias', [
            'nombre' => 'Categoria permitida',
        ])->assertCreated();
    }

    public function test_foundation_seeder_uses_project_roles_instead_of_template_roles(): void
    {
        $this->seed(FoundationSeeder::class);

        $this->assertDatabaseHas('roles', ['name' => 'admin', 'guard_name' => 'web']);
        $this->assertDatabaseHas('roles', ['name' => 'player', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('roles', ['name' => 'user', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'usuarios-ver', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'categorias-crear', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'jugadores-estadisticas-reiniciar', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('permissions', ['name' => 'user-list', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('permissions', ['name' => 'role-create', 'guard_name' => 'web']);
        $this->assertDatabaseMissing('permissions', ['name' => 'admin-stats-reset', 'guard_name' => 'web']);
    }

    public function test_seeded_player_cannot_list_users(): void
    {
        $this->seed(FoundationSeeder::class);

        $player = User::where('email', 'user@demo.com')->firstOrFail();

        $this->actingAs($player, 'sanctum');

        $this->getJson('/api/users')->assertForbidden();
    }

    public function test_user_with_categoria_create_permission_can_create_categories(): void
    {
        $user = $this->createUser('category-permission');
        $this->givePermission($user, 'categorias-crear');

        $this->actingAs($user, 'sanctum');

        $this->postJson('/api/categorias', [
            'nombre' => 'Categoria por permiso',
        ])->assertCreated();
    }

    public function test_sala_creator_can_update_own_sala_without_policy(): void
    {
        $creator = $this->createUser('sala-creator');
        $sala = Sala::create([
            'nombre' => 'Sala original',
            'codigo' => 'SEC-'.strtoupper(substr(uniqid(), -6)),
            'id_creador' => $creator->id,
        ]);

        $this->actingAs($creator, 'sanctum');

        $this->putJson('/api/salas/'.$sala->id, [
            'nombre' => 'Sala actualizada',
        ])
            ->assertOk()
            ->assertJsonPath('nombre', 'Sala actualizada');
    }

    public function test_user_cannot_update_another_users_sala_without_sala_edit_permission(): void
    {
        Permission::findOrCreate('salas-editar', 'web');
        $creator = $this->createUser('sala-owner');
        $otherUser = $this->createUser('sala-blocked');
        $sala = Sala::create([
            'nombre' => 'Sala privada',
            'codigo' => 'SEC-'.strtoupper(substr(uniqid(), -6)),
            'id_creador' => $creator->id,
        ]);

        $this->actingAs($otherUser, 'sanctum');

        $this->putJson('/api/salas/'.$sala->id, [
            'nombre' => 'Intento bloqueado',
        ])->assertForbidden();
    }

    public function test_user_with_sala_edit_permission_can_update_any_sala(): void
    {
        $creator = $this->createUser('sala-admin-owner');
        $admin = $this->createUser('sala-admin');
        $this->givePermission($admin, 'salas-editar');
        $sala = Sala::create([
            'nombre' => 'Sala administrable',
            'codigo' => 'SEC-'.strtoupper(substr(uniqid(), -6)),
            'id_creador' => $creator->id,
        ]);

        $this->actingAs($admin, 'sanctum');

        $this->putJson('/api/salas/'.$sala->id, [
            'nombre' => 'Sala administrada',
        ])
            ->assertOk()
            ->assertJsonPath('nombre', 'Sala administrada');
    }

    private function createUser(string $suffix): User
    {
        return User::factory()->create([
            'name' => 'Security '.$suffix,
            'surname1' => 'Feature',
            'surname2' => 'Suite',
            'email' => 'security-'.$suffix.'-'.uniqid().'@example.com',
        ]);
    }

    private function givePermission(User $user, string $permissionName): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::findOrCreate($permissionName, 'web');
        $role = Role::findOrCreate($permissionName.'-role', 'web');
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
