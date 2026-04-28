<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminStatsController extends Controller
{
    public function resetAll(Request $request, UserStatsService $userStatsService): JsonResponse
    {
        abort_unless(
            $this->canResetPlayerStats($request),
            403,
            'No tienes permiso para reiniciar las estadísticas.'
        );

        $userStatsService->resetAllPlayerStats();

        return response()->json([
            'message' => 'Las estadísticas de todos los jugadores se reiniciaron correctamente.',
        ]);
    }

    private function canResetPlayerStats(Request $request): bool
    {
        $user = $request->user();

        if (!$user) {
            return false;
        }

        return $user->getRoleNames()->contains(function (string $roleName): bool {
            $normalizedRole = Str::lower($roleName);

            return Str::contains($normalizedRole, 'admin') || $normalizedRole === 'docent';
        });
    }
}