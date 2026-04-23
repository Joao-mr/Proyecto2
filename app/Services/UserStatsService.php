<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserStatsService
{
    private const POINTS_PER_CORRECT_IMAGE = 50;
    private const STATS_COLUMNS = [
        'partidas_jugadas',
        'elo_total',
        'imagenes_acertadas',
        'promedio_puntos',
        'mejor_puntuacion',
        'ultima_puntuacion',
        'consistencia_pct',
    ];

    public function supportsPersistedStats(): bool
    {
        foreach (self::STATS_COLUMNS as $column) {
            if (!Schema::hasColumn('users', $column)) {
                return false;
            }
        }

        return true;
    }

    public function syncForUser(int $userId): void
    {
        if (!$this->supportsPersistedStats()) {
            return;
        }

        $aggregate = DB::table('usuario_partida')
            ->where('id_usuario', $userId)
            ->selectRaw('COUNT(*) as partidas_jugadas')
            ->selectRaw('COALESCE(SUM(puntuacion), 0) as elo_total')
            ->selectRaw('COALESCE(AVG(puntuacion), 0) as promedio_puntos')
            ->selectRaw('COALESCE(MAX(puntuacion), 0) as mejor_puntuacion')
            ->first();

        $ultimaPuntuacion = (int) (DB::table('usuario_partida as up')
            ->join('partidas as p', 'p.id', '=', 'up.id_partida')
            ->where('up.id_usuario', $userId)
            ->orderByDesc('p.fecha_inicio')
            ->orderByDesc('p.id')
            ->value('up.puntuacion') ?? 0);

        $partidasJugadas = (int) ($aggregate->partidas_jugadas ?? 0);
        $eloTotal = (int) ($aggregate->elo_total ?? 0);
        $promedio = (float) ($aggregate->promedio_puntos ?? 0);
        $mejorPuntuacion = (int) ($aggregate->mejor_puntuacion ?? 0);
        $consistencia = $mejorPuntuacion > 0
            ? min(100, (int) round(($promedio / $mejorPuntuacion) * 100))
            : 0;

        User::query()
            ->whereKey($userId)
            ->update([
                'partidas_jugadas' => $partidasJugadas,
                'elo_total' => $eloTotal,
                'imagenes_acertadas' => (int) floor($eloTotal / self::POINTS_PER_CORRECT_IMAGE),
                'promedio_puntos' => (int) round($promedio),
                'mejor_puntuacion' => $mejorPuntuacion,
                'ultima_puntuacion' => $ultimaPuntuacion,
                'consistencia_pct' => $consistencia,
            ]);
    }

    public function resetAllUserStats(): void
    {
        if (!$this->supportsPersistedStats()) {
            return;
        }

        DB::table('users')->update([
            'partidas_jugadas' => 0,
            'elo_total' => 0,
            'imagenes_acertadas' => 0,
            'promedio_puntos' => 0,
            'mejor_puntuacion' => 0,
            'ultima_puntuacion' => 0,
            'consistencia_pct' => 0,
        ]);
    }
}
