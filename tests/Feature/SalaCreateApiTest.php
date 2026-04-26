<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaCreateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_sala_and_becomes_creator(): void
    {
        $user = User::factory()->create(['surname1' => 'Tester']);
        $this->actingAs($user, 'sanctum');

        $payload = [
            'nombre' => 'Sala de prueba',
            'codigo' => 'SALA-' . strtoupper(substr(uniqid(), -6)),
            'tiempo_respuesta' => 30,
            'categorias' => [],
        ];

        $response = $this->postJson('/api/salas', $payload);

        $response->assertCreated()
            ->assertJsonPath('nombre', $payload['nombre'])
            ->assertJsonPath('id_creador', $user->id);

        $this->assertDatabaseHas('salas', [
            'nombre' => $payload['nombre'],
            'id_creador' => $user->id,
        ]);
    }
}
