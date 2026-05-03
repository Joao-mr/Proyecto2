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
        $recentActivity = $this->loadRecentActivity($user->id);
        $statsData = $this->loadStats($user, $userStatsService, $recentActivity);

        $currentTitle = $resolver->resolve($statsData['elo_total']);
        $nextTitle = $this->resolveNextTitle($statsData['elo_total']);

        return response()->json($this->buildStatsResponse(
            $statsData,
            $currentTitle,
            $nextTitle,
            $recentActivity
        ));
    }

    private function buildStatsResponse(array $statsData, array $currentTitle, ?array $nextTitle, $recentActivity): array
    {
        return [
            'partidas_jugadas' => $statsData['partidas_jugadas'],
            'elo_total' => $statsData['elo_total'],
            'imagenes_acertadas' => $statsData['imagenes_acertadas'],
            'titulo' => $currentTitle,
            'resumen' => [
                'promedio_puntos' => $statsData['promedio'],
                'mejor_puntuacion' => $statsData['mejor_puntuacion'],
                'ultima_puntuacion' => $statsData['ultima_puntuacion'],
                'consistencia_pct' => $statsData['consistencia'],
                'progreso_siguiente_titulo_pct' => $this->calculateNextTitleProgress($statsData['elo_total'], $currentTitle, $nextTitle),
            ],
            'actividad_reciente' => $recentActivity,
        ];
    }

    private function loadRecentActivity(int $userId)
    {
        return DB::table('usuario_partida as up')
            ->join('partidas as p', 'p.id', '=', 'up.id_partida')
            ->where('up.id_usuario', $userId)
            ->select('up.id_partida', 'up.puntuacion', 'p.fecha_inicio', 'p.fecha_fin')
            ->orderByDesc('p.fecha_inicio')
            ->orderByDesc('p.id')
            ->limit(self::RECENT_ACTIVITY_LIMIT)
            ->get();
    }

    private function loadStats($user, UserStatsService $userStatsService, $recentActivity): array
    {
        $userId = $user->id;

        if ($userStatsService->supportsPersistedStats()) {
            $stats = $userStatsService->getPersistedStatsForUser($user);
            return [
                'partidas_jugadas' => $stats['partidas_jugadas'],
                'elo_total' => $stats['elo_total'],
                'imagenes_acertadas' => $stats['imagenes_acertadas'],
                'promedio' => $stats['promedio_puntos'],
                'mejor_puntuacion' => $stats['mejor_puntuacion'],
                'ultima_puntuacion' => $stats['ultima_puntuacion'],
                'consistencia' => $stats['consistencia_pct'],
            ];
        }

        $stats = DB::table('usuario_partida as up')
            ->where('up.id_usuario', $userId)
            ->selectRaw('COUNT(*) as partidas_jugadas')
            ->selectRaw('COALESCE(SUM(up.puntuacion), 0) as elo_total')
            ->selectRaw('COALESCE(AVG(up.puntuacion), 0) as promedio_puntos')
            ->selectRaw('COALESCE(MAX(up.puntuacion), 0) as mejor_puntuacion')
            ->first();

        $eloTotal = (int) ($stats->elo_total ?? 0);
        $promedioFloat = (float) ($stats->promedio_puntos ?? 0);
        $mejorPuntuacion = (int) ($stats->mejor_puntuacion ?? 0);

        return [
            'partidas_jugadas' => (int) ($stats->partidas_jugadas ?? 0),
            'elo_total' => $eloTotal,
            'imagenes_acertadas' => $userStatsService->calculateCorrectImagesFromElo($eloTotal),
            'promedio' => (int) round($promedioFloat),
            'mejor_puntuacion' => $mejorPuntuacion,
            'ultima_puntuacion' => (int) ($recentActivity->first()->puntuacion ?? 0),
            'consistencia' => $mejorPuntuacion > 0
                ? min(100, (int) round(($promedioFloat / $mejorPuntuacion) * 100))
                : 0,
        ];
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

    public function abilities(Request $request)
    {
        return $request->user()->getAllPermissions()->pluck('name')->values();
    }
}
