<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_to_user_stats_returns_401(): void
    {
        $this->getJson('/api/user/stats')->assertUnauthorized();
    }

    public function test_user_with_no_partidas_returns_zero_stats_and_bronze_title(): void
    {
        Sanctum::actingAs($this->makeUser());

        $this->getJson('/api/user/stats')
            ->assertOk()
            ->assertJson([
                'partidas_jugadas' => 0,
                'elo_total' => 0,
                'imagenes_acertadas' => 0,
                'titulo' => [
                    'slug' => 'bronze',
                    'label' => 'Bronze',
                    'min_elo' => 0,
                ],
            ]);
    }

    public function test_user_stats_ignores_other_users_rows(): void
    {
        $user = $this->makeUser();
        $otherUser = $this->makeUser('other');

        Sanctum::actingAs($user);

        $sala = $this->createSala($user);
        $ownPartida = $this->createPartida($sala->id);
        $otherPartida = $this->createPartida($sala->id);

        $user->partidas()->attach($ownPartida->id, ['puntuacion' => 150]);
        $otherUser->partidas()->attach($otherPartida->id, ['puntuacion' => 999]);

        $this->getJson('/api/user/stats')
            ->assertOk()
            ->assertJson([
                'partidas_jugadas' => 1,
                'elo_total' => 150,
                'imagenes_acertadas' => 3,
                'titulo' => [
                    'slug' => 'bronze',
                    'label' => 'Bronze',
                    'min_elo' => 0,
                ],
            ]);
    }

    public function test_user_stats_title_threshold_500_resolves_silver(): void
    {
        $user = $this->makeUser();

        Sanctum::actingAs($user);

        $sala = $this->createSala($user);
        $partida = $this->createPartida($sala->id);

        $user->partidas()->attach($partida->id, ['puntuacion' => 500]);

        $this->getJson('/api/user/stats')
            ->assertOk()
            ->assertJson([
                'partidas_jugadas' => 1,
                'elo_total' => 500,
                'imagenes_acertadas' => 10,
                'titulo' => [
                    'slug' => 'silver',
                    'label' => 'Silver',
                    'min_elo' => 500,
                ],
            ]);
    }

    public function test_user_stats_returns_matches_elo_correct_images_and_title(): void
    {
        $user = $this->makeUser();

        Sanctum::actingAs($user);

        $sala = $this->createSala($user);
        $partida1 = $this->createPartida($sala->id);
        $partida2 = $this->createPartida($sala->id);

        $user->partidas()->attach($partida1->id, ['puntuacion' => 150]);
        $user->partidas()->attach($partida2->id, ['puntuacion' => 200]);

        $response = $this->getJson('/api/user/stats');

        $response->assertOk()
            ->assertJsonStructure([
                'partidas_jugadas',
                'elo_total',
                'imagenes_acertadas',
                'titulo' => [
                    'slug',
                    'label',
                    'min_elo',
                ],
            ])
            ->assertJson([
                'partidas_jugadas' => 2,
                'elo_total' => 350,
                'imagenes_acertadas' => 7,
                'titulo' => [
                    'slug' => 'bronze',
                    'label' => 'Bronze',
                    'min_elo' => 0,
                ],
            ]);
    }

    public function test_user_can_change_password_from_profile(): void
    {
        $user = $this->makeUser();

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user', [
            'name' => 'Updated Profile Name',
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'alias',
                    'name',
                    'surname1',
                    'surname2',
                    'email',
                    'roles',
                    'avatar',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.name', 'Updated Profile Name');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = $this->makeUser();

        Sanctum::actingAs($user);

        $this->putJson('/api/user', [
            'name' => 'Updated Profile Name',
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertFalse(Hash::check('new-password-123', $user->fresh()->password));
    }

    private function makeUser(string $suffix = ''): User
    {
        return User::factory()->create([
            'name' => 'Tester ' . $suffix,
            'surname1' => 'Profile',
            'surname2' => 'Suite',
            'email' => 'tester' . ($suffix !== '' ? '-' . $suffix : '') . '-' . uniqid() . '@example.com',
        ]);
    }

    private function createSala(User $user): Sala
    {
        return Sala::create([
            'nombre' => 'Sala stats ' . uniqid(),
            'codigo' => 'STATS-' . uniqid(),
            'id_creador' => $user->id,
        ]);
    }

    private function createPartida(int $salaId): Partida
    {
        return Partida::create([
            'id_sala' => $salaId,
        ]);
    }
}
