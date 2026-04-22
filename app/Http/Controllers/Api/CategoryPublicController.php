<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CategoryPublicController extends Controller
{
    public function index(): JsonResponse
    {
        $table = $this->resolveCategoryTable();

        if (!$table) {
            return response()->json(['data' => []]);
        }

        $nameCol = $this->firstExistingColumn($table, ['nombre', 'name', 'titulo', 'title']);
        $slugCol = $this->firstExistingColumn($table, ['slug', 'codigo']);
        $descCol = $this->firstExistingColumn($table, ['descripcion', 'description', 'detalle']);
        $imageCol = $this->firstExistingColumn($table, ['imagen', 'image', 'image_url', 'icono', 'thumbnail']);
        $orderCol = $this->firstExistingColumn($table, ['orden', 'position', 'sort', 'id']) ?? 'id';

        $rows = DB::table($table)
            ->orderBy($orderCol)
            ->get()
            ->map(function ($row) use ($nameCol, $slugCol, $descCol, $imageCol) {
                $name = trim((string) ($nameCol ? ($row->{$nameCol} ?? '') : ''));
                $description = trim((string) ($descCol ? ($row->{$descCol} ?? '') : ''));
                $slug = trim((string) ($slugCol ? ($row->{$slugCol} ?? '') : ''));
                $image = trim((string) ($imageCol ? ($row->{$imageCol} ?? '') : ''));

                $name = $name !== '' ? $name : 'Sin nombre';
                $slug = $slug !== '' ? $slug : Str::slug($name);

                return [
                    'slug' => $slug,
                    'name' => Str::upper($name),
                    'description' => $description,
                    'image' => $image !== '' ? $image : '/images/categoria-placeholder.webp',
                ];
            })
            ->values();

        return response()->json(['data' => $rows]);
    }

    private function resolveCategoryTable(): ?string
    {
        if (Schema::hasTable('categorias')) return 'categorias';
        if (Schema::hasTable('categories')) return 'categories';
        return null;
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}