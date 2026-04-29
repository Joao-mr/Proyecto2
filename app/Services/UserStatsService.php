<?php

namespace App\Services;

use App\Models\User;
use App\Support\PlayerTitleResolver;
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

    public function __construct(private readonly PlayerTitleResolver $playerTitleResolver)
    {
    }

    public function supportsPersistedStats(): bool
    {
        foreach (self::STATS_COLUMNS as $column) {
            if (!Schema::hasColumn('users', $column)) {
                return false;
            }
        }

        return true;
    }

    public function calculateCorrectImagesFromElo(int $eloTotal): int
    {
        return (int) floor($eloTotal / self::POINTS_PER_CORRECT_IMAGE);
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

        $title = $this->playerTitleResolver->resolve($eloTotal)['label'] ?? null;
        $updates = [];

        if (Schema::hasColumn('users', 'partidas_jugadas')) {
            $updates['partidas_jugadas'] = $partidasJugadas;
        }
        if (Schema::hasColumn('users', 'elo_total')) {
            $updates['elo_total'] = $eloTotal;
        }
        if (Schema::hasColumn('users', 'elo')) {
            $updates['elo'] = $eloTotal;
        }
        if (Schema::hasColumn('users', 'titulo')) {
            $updates['titulo'] = $title;
        }
        if (Schema::hasColumn('users', 'imagenes_acertadas')) {
            $updates['imagenes_acertadas'] = $this->calculateCorrectImagesFromElo($eloTotal);
        }
        if (Schema::hasColumn('users', 'promedio_puntos')) {
            $updates['promedio_puntos'] = (int) round($promedio);
        }
        if (Schema::hasColumn('users', 'mejor_puntuacion')) {
            $updates['mejor_puntuacion'] = $mejorPuntuacion;
        }
        if (Schema::hasColumn('users', 'ultima_puntuacion')) {
            $updates['ultima_puntuacion'] = $ultimaPuntuacion;
        }
        if (Schema::hasColumn('users', 'consistencia_pct')) {
            $updates['consistencia_pct'] = $consistencia;
        }

        if ($updates !== []) {
            User::query()
                ->whereKey($userId)
                ->update($updates);
        }
    }

    public function getPersistedStatsForUser(User $user): array
    {
        $this->syncForUser((int) $user->id);
        $user->refresh();

        $eloTotal = (int) ($user->elo_total ?? 0);

        return [
            'partidas_jugadas' => (int) ($user->partidas_jugadas ?? 0),
            'elo_total' => $eloTotal,
            'imagenes_acertadas' => (int) ($user->imagenes_acertadas ?? $this->calculateCorrectImagesFromElo($eloTotal)),
            'promedio_puntos' => (int) ($user->promedio_puntos ?? 0),
            'mejor_puntuacion' => (int) ($user->mejor_puntuacion ?? 0),
            'ultima_puntuacion' => (int) ($user->ultima_puntuacion ?? 0),
            'consistencia_pct' => (int) ($user->consistencia_pct ?? 0),
        ];
    }

    public function resetAllUserStats(): void
    {
        if (!$this->supportsPersistedStats()) {
            return;
        }

        $defaultTitle = $this->playerTitleResolver->resolve(0)['label'] ?? null;
        $updates = [];

        if (Schema::hasColumn('users', 'partidas_jugadas')) {
            $updates['partidas_jugadas'] = 0;
        }
        if (Schema::hasColumn('users', 'elo_total')) {
            $updates['elo_total'] = 0;
        }
        if (Schema::hasColumn('users', 'elo')) {
            $updates['elo'] = 0;
        }
        if (Schema::hasColumn('users', 'titulo')) {
            $updates['titulo'] = $defaultTitle;
        }
        if (Schema::hasColumn('users', 'imagenes_acertadas')) {
            $updates['imagenes_acertadas'] = 0;
        }
        if (Schema::hasColumn('users', 'promedio_puntos')) {
            $updates['promedio_puntos'] = 0;
        }
        if (Schema::hasColumn('users', 'mejor_puntuacion')) {
            $updates['mejor_puntuacion'] = 0;
        }
        if (Schema::hasColumn('users', 'ultima_puntuacion')) {
            $updates['ultima_puntuacion'] = 0;
        }
        if (Schema::hasColumn('users', 'consistencia_pct')) {
            $updates['consistencia_pct'] = 0;
        }

        if ($updates !== []) {
            DB::table('users')->update($updates);
        }
    }

    public function resetAllPlayerStats(): void
    {
        DB::transaction(function (): void {
            foreach (['usuario_partida', 'usuario_partidas'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }

            $this->resetAllUserStats();
        });
    }
}
