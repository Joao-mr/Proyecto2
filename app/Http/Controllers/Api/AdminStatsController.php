<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    public function resetAll(Request $request, UserStatsService $userStatsService): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('admin') && $user->rol !== 'admin') {
            abort(403);
        }

        $userStatsService->resetAllPlayerStats();

        return response()->json([
            'message' => 'Las estadísticas de todos los jugadores se reiniciaron correctamente.',
        ]);
    }
}
