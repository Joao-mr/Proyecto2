<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('elo')->default(0)->after('rol');
            $table->unsignedInteger('partidas_jugadas')->default(0)->after('elo');
            $table->string('titulo')->nullable()->after('partidas_jugadas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['elo', 'partidas_jugadas', 'titulo']);
        });
    }
};
