<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserStatsService;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends Controller
{
    public function resetAll(UserStatsService $userStatsService): JsonResponse
    {
        $this->authorize('jugadores-estadisticas-reiniciar');

        $userStatsService->resetAllPlayerStats();

        return response()->json([
            'message' => 'Las estadísticas de todos los jugadores se reiniciaron correctamente.',
        ]);
    }
}
