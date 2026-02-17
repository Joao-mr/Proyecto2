<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Sala;
use App\Models\SalaCategoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaCategoriaApiTest extends TestCase
{
    use RefreshDatabase;

    private function crearSalaYCategoria(): array
    {
        $user = User::create([
            'name' => 'Tester',
            'surname1' => 'API',
            'surname2' => 'Suite',
            'email' => 'tester'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $sala = Sala::create([
            'nombre' => 'Sala '.uniqid(),
            'codigo' => 'COD'.uniqid(),
            'id_creador' => $user->id,
        ]);

        $categoria = Categoria::create([
            'nombre' => 'Categoria '.uniqid(),
        ]);

        return [$sala, $categoria];
    }

    public function test_can_create_sala_categoria_relation(): void
    {
        [$sala, $categoria] = $this->crearSalaYCategoria();

        $response = $this->postJson('/api/sala-categorias', [
            'id_sala' => $sala->id,
            'id_categoria' => $categoria->id,
        ]);

        $response->assertCreated()
            ->assertJsonFragment([
                'id_sala' => $sala->id,
                'id_categoria' => $categoria->id,
            ])
            ->assertJsonStructure([
                'id_sala',
                'id_categoria',
                'sala',
                'categoria',
            ]);

        $this->assertDatabaseHas('sala_categorias', [
            'id_sala' => $sala->id,
            'id_categoria' => $categoria->id,
        ]);
    }

    public function test_cannot_create_duplicate_relation(): void
    {
        [$sala, $categoria] = $this->crearSalaYCategoria();

        SalaCategoria::create([
            'id_sala' => $sala->id,
            'id_categoria' => $categoria->id,
        ]);

        $response = $this->postJson('/api/sala-categorias', [
            'id_sala' => $sala->id,
            'id_categoria' => $categoria->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_can_list_and_show_relations(): void
    {
        [$sala, $categoria] = $this->crearSalaYCategoria();

        SalaCategoria::create([
            'id_sala' => $sala->id,
            'id_categoria' => $categoria->id,
        ]);

        $this->getJson('/api/sala-categorias')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id_sala',
                        'id_categoria',
                        'sala',
                        'categoria',
                    ],
                ],
            ]);

        $this->getJson("/api/sala-categorias/{$sala->id}/{$categoria->id}")
            ->assertOk()
            ->assertJsonFragment([
                'id_sala' => $sala->id,
                'id_categoria' => $categoria->id,
            ]);
    }

    public function test_can_update_relation_with_composite_key(): void
    {
        [$sala1, $categoria1] = $this->crearSalaYCategoria();
        [$sala2, $categoria2] = $this->crearSalaYCategoria();

        SalaCategoria::create([
            'id_sala' => $sala1->id,
            'id_categoria' => $categoria1->id,
        ]);

        $response = $this->putJson("/api/sala-categorias/{$sala1->id}/{$categoria1->id}", [
            'id_sala' => $sala2->id,
            'id_categoria' => $categoria2->id,
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'id_sala' => $sala2->id,
                'id_categoria' => $categoria2->id,
            ]);

        $this->assertDatabaseMissing('sala_categorias', [
            'id_sala' => $sala1->id,
            'id_categoria' => $categoria1->id,
        ]);

        $this->assertDatabaseHas('sala_categorias', [
            'id_sala' => $sala2->id,
            'id_categoria' => $categoria2->id,
        ]);
    }

    public function test_can_delete_relation(): void
    {
        [$sala, $categoria] = $this->crearSalaYCategoria();

        SalaCategoria::create([
            'id_sala' => $sala->id,
            'id_categoria' => $categoria->id,
        ]);

        $this->deleteJson("/api/sala-categorias/{$sala->id}/{$categoria->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('sala_categorias', [
            'id_sala' => $sala->id,
            'id_categoria' => $categoria->id,
        ]);
    }
}
