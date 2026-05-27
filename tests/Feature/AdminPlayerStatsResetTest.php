<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Sala;
use App\Models\User;
use App\Services\UserStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminPlayerStatsResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reset_all_player_stats_and_clear_match_history(): void
    {
        $admin = $this->makeUser('admin-'.uniqid().'@example.com');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate('admin-stats-reset', 'web');
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo('admin-stats-reset');
        $admin->assignRole('admin');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $player = $this->makeUser('player-'.uniqid().'@example.com');
        $partida = $this->createPartida($player);

        DB::table('usuario_partida')->insert([
            'id_usuario' => $player->id,
            'id_partida' => $partida->id,
            'puntuacion' => 200,
        ]);

        $this->app->make(UserStatsService::class)->syncForUser($player->id);

        Sanctum::actingAs($admin);

        $this->postJson('/api/admin/player-stats/reset')
            ->assertOk()
            ->assertJsonPath('message', 'Las estadísticas de todos los jugadores se reiniciaron correctamente.');

        $player->refresh();

        $this->assertSame(0, $player->partidas_jugadas);
        $this->assertSame(0, $player->elo_total);
        $this->assertSame(0, $player->imagenes_acertadas);
        $this->assertSame(0, $player->promedio_puntos);
        $this->assertSame(0, $player->mejor_puntuacion);
        $this->assertSame(0, $player->ultima_puntuacion);
        $this->assertSame(0, $player->consistencia_pct);
        $this->assertDatabaseCount('usuario_partida', 0);

        Sanctum::actingAs($player);

        $this->getJson('/api/user/stats')
            ->assertOk()
            ->assertJsonPath('partidas_jugadas', 0)
            ->assertJsonPath('elo_total', 0)
            ->assertJsonCount(0, 'actividad_reciente');
    }

    public function test_non_admin_cannot_reset_all_player_stats(): void
    {
        Sanctum::actingAs($this->makeUser('plain-'.uniqid().'@example.com'));

        $this->postJson('/api/admin/player-stats/reset')->assertForbidden();
    }

    private function makeUser(string $email): User
    {
        return User::factory()->create([
            'name' => 'Stats Reset Tester',
            'surname1' => 'Feature',
            'surname2' => 'Suite',
            'email' => $email,
        ]);
    }

    private function createPartida(User $user): Partida
    {
        $sala = Sala::create([
            'nombre' => 'Sala reset '.uniqid(),
            'codigo' => 'RESET-'.uniqid(),
            'id_creador' => $user->id,
        ]);

        return Partida::create([
            'id_sala' => $sala->id,
            'fecha_inicio' => now()->subMinute(),
            'fecha_fin' => now(),
        ]);
    }
}
