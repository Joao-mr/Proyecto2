<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
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
        Permission::findOrCreate('user-list', 'web');
        $user = $this->createUser('viewer');
        $otherUser = $this->createUser('other');

        $this->actingAs($user, 'sanctum');

        $this->getJson('/api/users/'.$otherUser->id)->assertForbidden();
    }

    public function test_user_with_user_list_permission_can_list_users(): void
    {
        $user = $this->createUser('admin-viewer');
        $this->givePermission($user, 'user-list');

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
        Role::findOrCreate('admin', 'web');
        $admin->assignRole('admin');

        $this->actingAs($admin, 'sanctum');

        $this->postJson('/api/categorias', [
            'nombre' => 'Categoria permitida',
        ])->assertCreated();
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
