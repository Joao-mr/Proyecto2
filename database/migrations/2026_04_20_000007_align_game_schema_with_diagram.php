<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('respuestas')) {
            Schema::drop('respuestas');
        }

        if (Schema::hasTable('salas')) {
            Schema::table('salas', function (Blueprint $table) {
                if (Schema::hasColumn('salas', 'created_at') && Schema::hasColumn('salas', 'updated_at')) {
                    $table->dropColumn(['created_at', 'updated_at']);
                }
            });
        }

        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table) {
                if (Schema::hasColumn('categorias', 'created_at') && Schema::hasColumn('categorias', 'updated_at')) {
                    $table->dropColumn(['created_at', 'updated_at']);
                }
            });
        }

        if (Schema::hasTable('partidas')) {
            Schema::table('partidas', function (Blueprint $table) {
                if (Schema::hasColumn('partidas', 'created_at') && Schema::hasColumn('partidas', 'updated_at')) {
                    $table->dropColumn(['created_at', 'updated_at']);
                }
            });
        }

        if (Schema::hasTable('imagenes')) {
            DB::table('imagenes')->whereNull('url')->update(['url' => '']);
            DB::table('imagenes')->whereNull('respuesta_correcta')->update(['respuesta_correcta' => '']);

            Schema::table('imagenes', function (Blueprint $table) {
                if (Schema::hasColumn('imagenes', 'created_at') && Schema::hasColumn('imagenes', 'updated_at')) {
                    $table->dropColumn(['created_at', 'updated_at']);
                }
            });

            DB::statement('ALTER TABLE imagenes MODIFY url VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE imagenes MODIFY respuesta_correcta VARCHAR(255) NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('salas')) {
            Schema::table('salas', function (Blueprint $table) {
                if (! Schema::hasColumn('salas', 'created_at') && ! Schema::hasColumn('salas', 'updated_at')) {
                    $table->timestamps();
                }
            });
        }

        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table) {
                if (! Schema::hasColumn('categorias', 'created_at') && ! Schema::hasColumn('categorias', 'updated_at')) {
                    $table->timestamps();
                }
            });
        }

        if (Schema::hasTable('partidas')) {
            Schema::table('partidas', function (Blueprint $table) {
                if (! Schema::hasColumn('partidas', 'created_at') && ! Schema::hasColumn('partidas', 'updated_at')) {
                    $table->timestamps();
                }
            });
        }

        if (Schema::hasTable('imagenes')) {
            Schema::table('imagenes', function (Blueprint $table) {
                if (! Schema::hasColumn('imagenes', 'created_at') && ! Schema::hasColumn('imagenes', 'updated_at')) {
                    $table->timestamps();
                }
            });

            DB::statement('ALTER TABLE imagenes MODIFY url VARCHAR(255) NULL');
            DB::statement('ALTER TABLE imagenes MODIFY respuesta_correcta VARCHAR(255) NULL');
        }

        if (! Schema::hasTable('respuestas')) {
            Schema::create('respuestas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_usuario');
                $table->unsignedBigInteger('id_imagen');
                $table->string('respuesta');
                $table->boolean('es_correcta')->default(false);
                $table->integer('tiempo')->default(0);
                $table->timestamps();

                $table->foreign('id_usuario')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('id_imagen')
                    ->references('id')
                    ->on('imagenes')
                    ->onDelete('cascade');
            });
        }
    }
};
