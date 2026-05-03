<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'elo')) {
                $table->unsignedInteger('elo')->default(0)->after('rol');
            }

            if (! Schema::hasColumn('users', 'partidas_jugadas')) {
                $table->unsignedInteger('partidas_jugadas')->default(0)->after('elo');
            }

            if (! Schema::hasColumn('users', 'titulo')) {
                $table->string('titulo')->nullable()->after('partidas_jugadas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $dropCandidates = ['elo', 'partidas_jugadas', 'titulo'];

            $columnsToDrop = array_values(array_filter(
                $dropCandidates,
                static fn (string $column): bool => Schema::hasColumn('users', $column)
            ));

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
