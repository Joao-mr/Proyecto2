<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('role_has_permissions') && Schema::hasTable('permissions')) {
            $legacyPermissionIds = DB::table('permissions')
                ->where(function ($query) {
                    $query->where('name', 'like', 'category-%')
                        ->orWhere('name', 'like', 'post-%')
                        ->orWhere('name', 'like', 'course-%')
                        ->orWhere('name', 'like', 'exercise-%')
                        ->orWhere('name', 'like', 'task-%');
                })
                ->pluck('id');

            if ($legacyPermissionIds->isNotEmpty()) {
                DB::table('role_has_permissions')->whereIn('permission_id', $legacyPermissionIds)->delete();
                DB::table('permissions')->whereIn('id', $legacyPermissionIds)->delete();
            }
        }

        Schema::dropIfExists('post_categories');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('post_categories')) {
            Schema::create('post_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }
};
