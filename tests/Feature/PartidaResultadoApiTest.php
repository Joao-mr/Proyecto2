<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PartidaResultadoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_result_with_sala_creates_partida_usuario_partida_and_updates_stats(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $sala = Sala::create([
            'nombre' => 'Sala test',
            'codigo' => 'SALA-' . uniqid(),
            'id_creador' => $user->id,
            'tiempo_respuesta' => 30,
        ]);

        $response = $this->postJson('/api/partidas/registrar-resultado', [
            'id_sala' => $sala->id,
            'puntuacion' => 200,
            'fecha_inicio' => now()->subMinutes(2)->toDateTimeString(),
            'fecha_fin' => now()->toDateTimeString(),
        ]);

        $response->assertCreated()
            ->assertJson([
                'id_sala' => $sala->id,
                'puntuacion' => 200,
            ]);

        $partidaId = (int) $response->json('id_partida');

        $this->assertDatabaseHas('partidas', [
            'id' => $partidaId,
            'id_sala' => $sala->id,
        ]);

        $this->assertDatabaseHas('usuario_partida', [
            'id_usuario' => $user->id,
            'id_partida' => $partidaId,
            'puntuacion' => 200,
        ]);

        $user->refresh();
        $this->assertSame(1, (int) $user->partidas_jugadas);
        $this->assertSame(200, (int) $user->elo_total);
    }

    public function test_register_result_with_categoria_creates_technical_sala_if_needed(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $categoria = Categoria::create([
            'nombre' => 'Categoria sin sala',
        ]);

        $response = $this->postJson('/api/partidas/registrar-resultado', [
            'id_categoria' => $categoria->id,
            'puntuacion' => 150,
        ]);

        $response->assertCreated()
            ->assertJson([
                'puntuacion' => 150,
            ]);

        $idSala = (int) $response->json('id_sala');
        $idPartida = (int) $response->json('id_partida');

        $this->assertDatabaseHas('salas', [
            'id' => $idSala,
            'id_creador' => $user->id,
        ]);

        $this->assertDatabaseHas('sala_categorias', [
            'id_sala' => $idSala,
            'id_categoria' => $categoria->id,
        ]);

        $this->assertDatabaseHas('partidas', [
            'id' => $idPartida,
            'id_sala' => $idSala,
        ]);

        $this->assertDatabaseHas('usuario_partida', [
            'id_usuario' => $user->id,
            'id_partida' => $idPartida,
            'puntuacion' => 150,
        ]);
    }

    private function makeUser(): User
    {
        return User::factory()->create([
            'name' => 'Match Tester',
            'surname1' => 'Feature',
            'surname2' => 'Suite',
            'email' => 'match-' . uniqid() . '@example.com',
        ]);
    }
}
