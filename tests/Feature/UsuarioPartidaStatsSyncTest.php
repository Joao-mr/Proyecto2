<?php

namespace Tests\Feature;

use App\Models\Partida;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UsuarioPartidaStatsSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_usuario_partida_syncs_user_stats_columns(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $partida = $this->createPartida($user);

        $this->postJson('/api/usuario-partidas', [
            'id_partida' => $partida->id,
            'puntuacion' => 150,
        ])->assertCreated();

        $user->refresh();

        $this->assertSame(1, $user->partidas_jugadas);
        $this->assertSame(150, $user->elo_total);
        $this->assertSame(3, $user->imagenes_acertadas);
        $this->assertSame(150, $user->promedio_puntos);
        $this->assertSame(150, $user->mejor_puntuacion);
        $this->assertSame(150, $user->ultima_puntuacion);
        $this->assertSame(100, $user->consistencia_pct);
    }

    public function test_update_and_delete_usuario_partida_resync_user_stats_columns(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $partida1 = $this->createPartida($user, now()->subMinutes(20), now()->subMinutes(19));
        $partida2 = $this->createPartida($user, now()->subMinutes(10), now()->subMinutes(9));

        $this->postJson('/api/usuario-partidas', [
            'id_partida' => $partida1->id,
            'puntuacion' => 100,
        ])->assertCreated();

        $this->postJson('/api/usuario-partidas', [
            'id_partida' => $partida2->id,
            'puntuacion' => 200,
        ])->assertCreated();

        $this->putJson('/api/usuario-partidas/' . $partida1->id, [
            'puntuacion' => 250,
        ])->assertOk();

        $user->refresh();
        $this->assertSame(2, $user->partidas_jugadas);
        $this->assertSame(450, $user->elo_total);
        $this->assertSame(9, $user->imagenes_acertadas);
        $this->assertSame(225, $user->promedio_puntos);
        $this->assertSame(250, $user->mejor_puntuacion);
        $this->assertSame(200, $user->ultima_puntuacion);
        $this->assertSame(90, $user->consistencia_pct);

        $this->deleteJson('/api/usuario-partidas/' . $partida2->id)->assertNoContent();

        $user->refresh();
        $this->assertSame(1, $user->partidas_jugadas);
        $this->assertSame(250, $user->elo_total);
        $this->assertSame(5, $user->imagenes_acertadas);
        $this->assertSame(250, $user->promedio_puntos);
        $this->assertSame(250, $user->mejor_puntuacion);
        $this->assertSame(250, $user->ultima_puntuacion);
        $this->assertSame(100, $user->consistencia_pct);
    }

    private function makeUser(): User
    {
        return User::factory()->create([
            'name' => 'Stats Tester',
            'surname1' => 'Integration',
            'surname2' => 'Suite',
            'email' => 'stats-' . uniqid() . '@example.com',
        ]);
    }

    private function createPartida(User $user, $fechaInicio = null, $fechaFin = null): Partida
    {
        $sala = Sala::create([
            'nombre' => 'Sala sync ' . uniqid(),
            'codigo' => 'SYNC-' . uniqid(),
            'id_creador' => $user->id,
        ]);

        return Partida::create([
            'id_sala' => $sala->id,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
        ]);
    }
}
