<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('imagenes')) {
            return;
        }

        if (!Schema::hasColumn('imagenes', 'categoria_id')) {
            Schema::table('imagenes', function (Blueprint $table) {
                $table->unsignedBigInteger('categoria_id')->nullable()->after('respuesta_correcta');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('imagenes')) {
            return;
        }

        if (Schema::hasColumn('imagenes', 'categoria_id')) {
            Schema::table('imagenes', function (Blueprint $table) {
                $table->dropColumn('categoria_id');
            });
        }
    }
};
    