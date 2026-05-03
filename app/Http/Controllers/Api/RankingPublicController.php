<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PlayerTitleResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RankingPublicController extends Controller
{
    public function index(Request $request, PlayerTitleResolver $resolver): JsonResponse
    {
        $mode = $request->query('mode', 'individual');
        if (!in_array($mode, ['individual', 'multijugador'], true)) {
            $mode = 'individual';
        }

        if (!Schema::hasTable('users')) {
            return response()->json(['mode' => $mode, 'data' => []]);
        }

        $columns = $this->resolveIndexColumns($mode);
        $limit = max(1, min((int) $request->query('limit', 10), 100));

        $query = DB::table('users')->select($columns['select']);
        if ($columns['name'] !== 'id') {
            $query->whereNotNull($columns['name']);
        }
        $query->orderByDesc($columns['elo'] ?? 'id');

        $data = $query->limit($limit)->get()
            ->map(fn($row) => $this->formatIndexRow($row, $resolver))
            ->values();

        return response()->json(['mode' => $mode, 'data' => $data]);
    }

    private function resolveIndexColumns(string $mode): array
    {
        $nameCol = $this->firstExistingColumn('users', ['name', 'username', 'nick', 'nombre']) ?? 'id';
        $eloCol = $this->firstExistingColumn('users', $mode === 'multijugador'
            ? ['elo_multijugador', 'elo_multi', 'rating_multijugador', 'rating_multi', 'elo', 'rating']
            : ['elo_individual', 'rating_individual', 'elo', 'rating']);
        $matchesCol = $this->firstExistingColumn('users', $mode === 'multijugador'
            ? ['partidas_multijugador', 'matches_multijugador', 'partidas_jugadas', 'matches_played']
            : ['partidas_individuales', 'matches_individuales', 'partidas_jugadas', 'matches_played']);
        $titleCol = $this->firstExistingColumn('users', $mode === 'multijugador'
            ? ['titulo_multijugador', 'title_multijugador', 'rango_multijugador', 'titulo', 'title']
            : ['titulo_individual', 'title_individual', 'rango_individual', 'titulo', 'title']);

        $select = ["{$nameCol} as name"];
        if ($eloCol) $select[] = "{$eloCol} as elo";
        if ($matchesCol) $select[] = "{$matchesCol} as matches";
        if ($titleCol) $select[] = "{$titleCol} as title";

        return ['name' => $nameCol, 'elo' => $eloCol, 'select' => $select];
    }

    private function formatIndexRow(object $row, PlayerTitleResolver $resolver): array
    {
        $elo = (int) ($row->elo ?? 0);
        $name = trim((string) ($row->name ?? ''));
        $storedTitle = trim((string) ($row->title ?? ''));
        $resolvedTitle = (string) ($resolver->resolve($elo)['label'] ?? 'Bronce');

        return [
            'name' => strtoupper($name !== '' ? $name : 'SIN NOMBRE'),
            'elo' => $elo,
            'matches' => (int) ($row->matches ?? 0),
            'title' => strtoupper($storedTitle !== '' ? $storedTitle : $resolvedTitle),
        ];
    }

    public function category(Request $request, $categoria = null): JsonResponse
    {
        $categoriaId = $categoria ?? $request->query('categoria_id');
        if ($categoriaId === null || $categoriaId === '') {
            return response()->json(['category_id' => null, 'data' => []]);
        }

        $limit = max(1, min((int) $request->query('limit', 50), 200));

        $upTable = $this->firstExistingTable(['usuario_partidas', 'usuario_partida']);
        $partidasTable = $this->firstExistingTable(['partidas']);
        $salaCategoriaTable = $this->firstExistingTable(['sala_categoria', 'sala_categorias']);

        if (!$upTable || !$partidasTable || !$salaCategoriaTable || !Schema::hasTable('users')) {
            return response()->json(['category_id' => $categoriaId, 'data' => []]);
        }

        $upUserCol = $this->firstExistingColumn($upTable, ['user_id', 'usuario_id', 'idUsuario', 'id_usuario']);
        $upPartidaCol = $this->firstExistingColumn($upTable, ['partida_id', 'idPartida', 'id_partida']);
        $upScoreCol = $this->firstExistingColumn($upTable, ['puntuacion_total', 'puntuacion', 'score', 'puntos']);

        $pIdCol = $this->firstExistingColumn($partidasTable, ['id', 'idPartida']) ?? 'id';
        $pSalaCol = $this->firstExistingColumn($partidasTable, ['sala_id', 'idSala', 'id_sala']);

        $scSalaCol = $this->firstExistingColumn($salaCategoriaTable, ['sala_id', 'idSala', 'id_sala']);
        $scCategoriaCol = $this->firstExistingColumn($salaCategoriaTable, ['categoria_id', 'idCategoria', 'id_categoria']);

        $uIdCol = $this->firstExistingColumn('users', ['id', 'idUsuario']) ?? 'id';
        $uNameCol = $this->firstExistingColumn('users', ['name', 'username', 'nick', 'nombre']) ?? $uIdCol;

        if (!$upUserCol || !$upPartidaCol || !$pSalaCol || !$scSalaCol || !$scCategoriaCol) {
            return response()->json(['category_id' => $categoriaId, 'data' => []]);
        }

        $scoreExpr = $upScoreCol
            ? "COALESCE(SUM(up.`{$upScoreCol}`), 0)"
            : "COUNT(up.`{$upPartidaCol}`)";

        $rows = DB::table("{$upTable} as up")
            ->join("{$partidasTable} as p", "up.{$upPartidaCol}", '=', "p.{$pIdCol}")
            ->join("{$salaCategoriaTable} as sc", "p.{$pSalaCol}", '=', "sc.{$scSalaCol}")
            ->join("users as u", "up.{$upUserCol}", '=', "u.{$uIdCol}")
            ->where("sc.{$scCategoriaCol}", $categoriaId)
            ->selectRaw(
                "u.`{$uIdCol}` as user_id,
                 u.`{$uNameCol}` as usuario,
                 {$scoreExpr} as puntuacion_total,
                 COUNT(up.`{$upPartidaCol}`) as partidas_jugadas"
            )
            ->groupBy("u.{$uIdCol}", "u.{$uNameCol}")
            ->orderByDesc('puntuacion_total')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $usuario = trim((string) ($row->usuario ?? ''));
                if ($usuario === '') $usuario = 'SIN NOMBRE';

                return [
                    'user_id' => $row->user_id,
                    'usuario' => strtoupper($usuario),
                    'puntuacion_total' => (int) ($row->puntuacion_total ?? 0),
                    'partidas_jugadas' => (int) ($row->partidas_jugadas ?? 0),
                ];
            })
            ->values();

        return response()->json([
            'category_id' => $categoriaId,
            'data' => $rows,
        ]);
    }

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }
        return null;
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }
        return null;
    }
}
