<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\UserStatsService;
use App\Support\PlayerTitleResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{
    private const RECENT_ACTIVITY_LIMIT = 8;

    /** */
    public function update(UpdateProfileRequest $request)
    {
        $profile = $request->user();
        $profile->name = $request->name;

        if ($request->filled('password')) {
            $profile->password = Hash::make($request->password);
        }

        $profile->save();

        return new UserResource($profile->load('roles'));
    }

    public function user(Request $request)
    {
        $user = $request->user()->load('roles');
        return new UserResource($user);
    }

    public function stats(Request $request, PlayerTitleResolver $resolver, UserStatsService $userStatsService)
    {
        $user = $request->user();
        $userId = $user->id;

        $recentActivity = DB::table('usuario_partida as up')
            ->join('partidas as p', 'p.id', '=', 'up.id_partida')
            ->where('up.id_usuario', $userId)
            ->select('up.id_partida', 'up.puntuacion', 'p.fecha_inicio', 'p.fecha_fin')
            ->orderByDesc('p.fecha_inicio')
            ->orderByDesc('p.id')
            ->limit(self::RECENT_ACTIVITY_LIMIT)
            ->get();

        if ($userStatsService->supportsPersistedStats()) {
            $stats = $userStatsService->getPersistedStatsForUser($user);

            $partidasJugadas = $stats['partidas_jugadas'];
            $eloTotal = $stats['elo_total'];
            $imagenesAcertadas = $stats['imagenes_acertadas'];
            $promedio = $stats['promedio_puntos'];
            $mejorPuntuacion = $stats['mejor_puntuacion'];
            $ultimaPuntuacion = $stats['ultima_puntuacion'];
            $consistencia = $stats['consistencia_pct'];
        } else {
            $stats = DB::table('usuario_partida as up')
                ->where('up.id_usuario', $userId)
                ->selectRaw('COUNT(*) as partidas_jugadas')
                ->selectRaw('COALESCE(SUM(up.puntuacion), 0) as elo_total')
                ->selectRaw('COALESCE(AVG(up.puntuacion), 0) as promedio_puntos')
                ->selectRaw('COALESCE(MAX(up.puntuacion), 0) as mejor_puntuacion')
                ->first();

            $partidasJugadas = (int) ($stats->partidas_jugadas ?? 0);
            $eloTotal = (int) ($stats->elo_total ?? 0);
            $imagenesAcertadas = $userStatsService->calculateCorrectImagesFromElo($eloTotal);
            $promedioFloat = (float) ($stats->promedio_puntos ?? 0);
            $promedio = (int) round($promedioFloat);
            $mejorPuntuacion = (int) ($stats->mejor_puntuacion ?? 0);
            $ultimaPuntuacion = (int) ($recentActivity->first()->puntuacion ?? 0);
            $consistencia = $mejorPuntuacion > 0
                ? min(100, (int) round(($promedioFloat / $mejorPuntuacion) * 100))
                : 0;
        }

        $currentTitle = $resolver->resolve($eloTotal);
        $nextTitle = $this->resolveNextTitle($eloTotal);

        return response()->json([
            'partidas_jugadas' => $partidasJugadas,
            'elo_total' => $eloTotal,
            'imagenes_acertadas' => $imagenesAcertadas,
            'titulo' => $currentTitle,
            'resumen' => [
                'promedio_puntos' => $promedio,
                'mejor_puntuacion' => $mejorPuntuacion,
                'ultima_puntuacion' => $ultimaPuntuacion,
                'consistencia_pct' => $consistencia,
                'progreso_siguiente_titulo_pct' => $this->calculateNextTitleProgress($eloTotal, $currentTitle, $nextTitle),
            ],
            'actividad_reciente' => $recentActivity,
        ]);
    }

    private function resolveNextTitle(int $eloTotal): ?array
    {
        $tiers = collect(config('player_titles', []))
            ->values()
            ->filter(function ($tier): bool {
                return is_array($tier) && array_key_exists('min_elo', $tier);
            })
            ->sortBy('min_elo')
            ->values();

        return $tiers
            ->first(function ($tier) use ($eloTotal): bool {
                return $eloTotal < (int) ($tier['min_elo'] ?? 0);
            });
    }

    private function calculateNextTitleProgress(int $eloTotal, array $currentTitle, ?array $nextTitle): int
    {
        if (!$nextTitle) {
            return 100;
        }

        $currentMinElo = (int) ($currentTitle['min_elo'] ?? 0);
        $nextMinElo = (int) ($nextTitle['min_elo'] ?? 0);
        $range = max(1, $nextMinElo - $currentMinElo);
        $progress = (int) round((($eloTotal - $currentMinElo) / $range) * 100);

        return max(0, min(100, $progress));
    }
}
