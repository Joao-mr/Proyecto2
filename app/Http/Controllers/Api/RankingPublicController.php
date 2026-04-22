<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RankingPublicController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $mode = $request->query('mode', 'individual');
        if (!in_array($mode, ['individual', 'multijugador'], true)) {
            $mode = 'individual';
        }

        if (!Schema::hasTable('users')) {
            return response()->json(['mode' => $mode, 'data' => []]);
        }

        $nameCol = $this->firstExistingColumn('users', ['name', 'username', 'nick', 'nombre']) ?? 'id';

        $eloCol = $this->firstExistingColumn(
            'users',
            $mode === 'multijugador'
                ? ['elo_multijugador', 'elo_multi', 'rating_multijugador', 'rating_multi', 'elo', 'rating']
                : ['elo_individual', 'rating_individual', 'elo', 'rating']
        );

        $matchesCol = $this->firstExistingColumn(
            'users',
            $mode === 'multijugador'
                ? ['partidas_multijugador', 'matches_multijugador', 'partidas_jugadas', 'matches_played']
                : ['partidas_individuales', 'matches_individuales', 'partidas_jugadas', 'matches_played']
        );

        $titleCol = $this->firstExistingColumn(
            'users',
            $mode === 'multijugador'
                ? ['titulo_multijugador', 'title_multijugador', 'rango_multijugador', 'titulo', 'title']
                : ['titulo_individual', 'title_individual', 'rango_individual', 'titulo', 'title']
        );

        $limit = max(1, min((int) $request->query('limit', 10), 100));

        $query = DB::table('users');
        $select = ["{$nameCol} as name"];

        if ($eloCol) $select[] = "{$eloCol} as elo";
        if ($matchesCol) $select[] = "{$matchesCol} as matches";
        if ($titleCol) $select[] = "{$titleCol} as title";

        $rows = $query->select($select);

        if ($nameCol !== 'id') {
            $rows->whereNotNull($nameCol);
        }

        if ($eloCol) {
            $rows->orderByDesc($eloCol);
        } else {
            $rows->orderByDesc('id');
        }

        $data = $rows
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $elo = (int) ($row->elo ?? 0);
                $name = trim((string) ($row->name ?? ''));

                if ($name === '') $name = 'SIN NOMBRE';

                return [
                    'name' => strtoupper($name),
                    'elo' => $elo,
                    'matches' => (int) ($row->matches ?? 0),
                    'title' => (string) ($row->title ?? $this->titleByElo($elo)),
                ];
            })
            ->values();

        return response()->json([
            'mode' => $mode,
            'data' => $data,
        ]);
    }

    private function titleByElo(int $elo): string
    {
        return match (true) {
            $elo >= 13000 => 'RADIANT',
            $elo >= 11000 => 'MASTER',
            $elo >= 10000 => 'UNREAL',
            $elo >= 9000 => 'CHALLENGER',
            default => 'CHAMPION',
        };
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