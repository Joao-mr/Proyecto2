<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Partida;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioPartidaStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resource_includes_profile_stats_fields(): void
    {
        $user = User::factory()->create([
            'surname1' => 'Tester',
            'elo' => 1234,
            'partidas_jugadas' => 9,
            'titulo' => 'PLATINUM',
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/users/' . $user->id);

        $response->assertOk()
            ->assertJsonPath('data.elo', 1234)
            ->assertJsonPath('data.partidas_jugadas', 9)
            ->assertJsonPath('data.titulo', 'PLATINUM');
    }

    public function test_store_usuario_partida_recalculates_user_stats(): void
    {
        $user = User::factory()->create(['surname1' => 'Tester']);
        $this->actingAs($user, 'sanctum');

        $firstPartida = $this->createPartidaForUser($user);
        $secondPartida = $this->createPartidaForUser($user);

        $this->postJson('/api/usuario-partidas', [
            'id_partida' => $firstPartida->id,
            'puntuacion' => 350,
        ])->assertCreated();

        $this->postJson('/api/usuario-partidas', [
            'id_partida' => $secondPartida->id,
            'puntuacion' => 200,
        ])->assertCreated();

        $user->refresh();

        $this->assertSame(550, $user->elo);
        $this->assertSame(2, $user->partidas_jugadas);
        $this->assertSame('BRONZE', $user->titulo);
    }

    public function test_update_and_destroy_usuario_partida_recalculate_user_stats(): void
    {
        $user = User::factory()->create(['surname1' => 'Tester']);
        $this->actingAs($user, 'sanctum');

        $firstPartida = $this->createPartidaForUser($user);
        $secondPartida = $this->createPartidaForUser($user);

        $this->postJson('/api/usuario-partidas', [
            'id_partida' => $firstPartida->id,
            'puntuacion' => 100,
        ])->assertCreated();

        $this->postJson('/api/usuario-partidas', [
            'id_partida' => $secondPartida->id,
            'puntuacion' => 400,
        ])->assertCreated();

        $this->putJson('/api/usuario-partidas/' . $firstPartida->id, [
            'puntuacion' => 300,
        ])->assertOk();

        $user->refresh();
        $this->assertSame(700, $user->elo);
        $this->assertSame(2, $user->partidas_jugadas);
        $this->assertSame('BRONZE', $user->titulo);

        $this->deleteJson('/api/usuario-partidas/' . $secondPartida->id)->assertNoContent();

        $user->refresh();
        $this->assertSame(300, $user->elo);
        $this->assertSame(1, $user->partidas_jugadas);
        $this->assertSame('BRONZE', $user->titulo);
    }

    public function test_finish_endpoint_saves_match_and_updates_stats_by_sala(): void
    {
        $user = User::factory()->create(['surname1' => 'Tester']);
        $this->actingAs($user, 'sanctum');
        $sala = $this->createSalaForUser($user);

        $response = $this->postJson('/api/usuario-partidas/finalizar', [
            'id_sala' => $sala->id,
            'puntuacion' => 420,
        ]);

        $response->assertCreated();

        $this->assertDatabaseCount('partidas', 1);
        $this->assertDatabaseHas('usuario_partida', [
            'id_usuario' => $user->id,
            'puntuacion' => 420,
        ]);

        $user->refresh();
        $this->assertSame(420, $user->elo);
        $this->assertSame(1, $user->partidas_jugadas);
        $this->assertSame('BRONZE', $user->titulo);
    }

    public function test_finish_endpoint_resolves_sala_by_category(): void
    {
        $user = User::factory()->create(['surname1' => 'Tester']);
        $this->actingAs($user, 'sanctum');

        $sala = $this->createSalaForUser($user);
        $categoria = Categoria::create(['nombre' => 'Categoria Test']);
        DB::table('sala_categorias')->insert([
            'id_sala' => $sala->id,
            'id_categoria' => $categoria->id,
        ]);

        $response = $this->postJson('/api/usuario-partidas/finalizar', [
            'id_categoria' => $categoria->id,
            'puntuacion' => 530,
        ]);

        $response->assertCreated()
            ->assertJsonPath('id_sala', $sala->id);

        $user->refresh();
        $this->assertSame(530, $user->elo);
        $this->assertSame(1, $user->partidas_jugadas);
        $this->assertSame('BRONZE', $user->titulo);
    }

    private function createPartidaForUser(User $user): Partida
    {
        $sala = $this->createSalaForUser($user);

        return Partida::create([
            'id_sala' => $sala->id,
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
        ]);
    }

    private function createSalaForUser(User $user): Sala
    {
        return Sala::create([
            'nombre' => 'Sala test ' . uniqid('', true),
            'codigo' => 'CODE' . str_replace('.', '', uniqid('', true)),
            'id_creador' => $user->id,
            'tiempo_respuesta' => 30,
        ]);
    }
}
