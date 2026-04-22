<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'partidas_jugadas')) {
                $table->unsignedInteger('partidas_jugadas')->default(0)->after('rol');
            }

            if (!Schema::hasColumn('users', 'elo_total')) {
                $table->unsignedInteger('elo_total')->default(0)->after('partidas_jugadas');
            }

            if (!Schema::hasColumn('users', 'imagenes_acertadas')) {
                $table->unsignedInteger('imagenes_acertadas')->default(0)->after('elo_total');
            }

            if (!Schema::hasColumn('users', 'promedio_puntos')) {
                $table->unsignedInteger('promedio_puntos')->default(0)->after('imagenes_acertadas');
            }

            if (!Schema::hasColumn('users', 'mejor_puntuacion')) {
                $table->unsignedInteger('mejor_puntuacion')->default(0)->after('promedio_puntos');
            }

            if (!Schema::hasColumn('users', 'ultima_puntuacion')) {
                $table->unsignedInteger('ultima_puntuacion')->default(0)->after('mejor_puntuacion');
            }

            if (!Schema::hasColumn('users', 'consistencia_pct')) {
                $table->unsignedTinyInteger('consistencia_pct')->default(0)->after('ultima_puntuacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $dropCandidates = [
                'partidas_jugadas',
                'elo_total',
                'imagenes_acertadas',
                'promedio_puntos',
                'mejor_puntuacion',
                'ultima_puntuacion',
                'consistencia_pct',
            ];

            $columnsToDrop = array_values(array_filter($dropCandidates, fn(string $column): bool => Schema::hasColumn('users', $column)));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
