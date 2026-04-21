<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Support\PlayerTitleResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{
    private const POINTS_PER_CORRECT_IMAGE = 50;

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

    public function stats(Request $request, PlayerTitleResolver $resolver)
    {
        $stats = DB::table('usuario_partida')
            ->where('id_usuario', $request->user()->id)
            ->selectRaw('COUNT(*) as partidas_jugadas, COALESCE(SUM(puntuacion), 0) as elo_total')
            ->first();

        $eloTotal = (int) ($stats->elo_total ?? 0);

        return response()->json([
            'partidas_jugadas' => (int) ($stats->partidas_jugadas ?? 0),
            'elo_total' => $eloTotal,
            'imagenes_acertadas' => (int) floor($eloTotal / self::POINTS_PER_CORRECT_IMAGE),
            'titulo' => $resolver->resolve($eloTotal),
        ]);
    }
}
