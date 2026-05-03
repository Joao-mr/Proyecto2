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
            $rowsInRespuestas = DB::table('respuestas')->count();

            if ($rowsInRespuestas > 0) {
                throw new \RuntimeException(
                    sprintf(
                        'Cannot drop table "respuestas" because it contains %d row(s). Backup/migrate data first, then rerun migration 2026_04_20_000007_align_game_schema_with_diagram.',
                        $rowsInRespuestas
                    )
                );
            }

            Schema::drop('respuestas');
        }

        if (Schema::hasTable('salas')) {
            Schema::table('salas', function (Blueprint $table) {
                $columnsToDrop = [];

                if (Schema::hasColumn('salas', 'created_at')) {
                    $columnsToDrop[] = 'created_at';
                }

                if (Schema::hasColumn('salas', 'updated_at')) {
                    $columnsToDrop[] = 'updated_at';
                }

                if ($columnsToDrop !== []) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }

        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table) {
                $columnsToDrop = [];

                if (Schema::hasColumn('categorias', 'created_at')) {
                    $columnsToDrop[] = 'created_at';
                }

                if (Schema::hasColumn('categorias', 'updated_at')) {
                    $columnsToDrop[] = 'updated_at';
                }

                if ($columnsToDrop !== []) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }

        if (Schema::hasTable('partidas')) {
            Schema::table('partidas', function (Blueprint $table) {
                $columnsToDrop = [];

                if (Schema::hasColumn('partidas', 'created_at')) {
                    $columnsToDrop[] = 'created_at';
                }

                if (Schema::hasColumn('partidas', 'updated_at')) {
                    $columnsToDrop[] = 'updated_at';
                }

                if ($columnsToDrop !== []) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }

        if (Schema::hasTable('imagenes')) {
            Schema::table('imagenes', function (Blueprint $table) {
                $columnsToDrop = [];

                if (Schema::hasColumn('imagenes', 'created_at')) {
                    $columnsToDrop[] = 'created_at';
                }

                if (Schema::hasColumn('imagenes', 'updated_at')) {
                    $columnsToDrop[] = 'updated_at';
                }

                if ($columnsToDrop !== []) {
                    $table->dropColumn($columnsToDrop);
                }
            });

            if ($this->isMySqlCompatibleDriver()) {
                DB::statement('ALTER TABLE imagenes MODIFY url VARCHAR(255) NOT NULL');
                DB::statement('ALTER TABLE imagenes MODIFY respuesta_correcta VARCHAR(255) NOT NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('salas')) {
            Schema::table('salas', function (Blueprint $table) {
                if (! Schema::hasColumn('salas', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('salas', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('categorias')) {
            Schema::table('categorias', function (Blueprint $table) {
                if (! Schema::hasColumn('categorias', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('categorias', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('partidas')) {
            Schema::table('partidas', function (Blueprint $table) {
                if (! Schema::hasColumn('partidas', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('partidas', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('imagenes')) {
            Schema::table('imagenes', function (Blueprint $table) {
                if (! Schema::hasColumn('imagenes', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('imagenes', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });

            if ($this->isMySqlCompatibleDriver()) {
                DB::statement('ALTER TABLE imagenes MODIFY url VARCHAR(255) NULL');
                DB::statement('ALTER TABLE imagenes MODIFY respuesta_correcta VARCHAR(255) NULL');
            }
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

    private function isMySqlCompatibleDriver(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
