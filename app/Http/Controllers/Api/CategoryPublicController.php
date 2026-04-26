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
        if (!Schema::hasTable('categorias')) {
            return response()->json(['data' => []]);
        }

        $table = 'categorias';
        $idCol = 'id';
        $nameCol = $this->firstExistingColumn($table, ['nombre']);
        $slugCol = $this->firstExistingColumn($table, ['slug']);
        $descCol = $this->firstExistingColumn($table, ['descripcion']);
        $imageCol = $this->firstExistingColumn($table, ['imagen']);
        $orderCol = $this->firstExistingColumn($table, ['orden', 'id']) ?? 'id';

        $rows = DB::table($table)
            ->orderBy($orderCol)
            ->get()
            ->map(function ($row) use ($idCol, $nameCol, $slugCol, $descCol, $imageCol) {
                $id = $idCol ? ($row->{$idCol} ?? null) : null;
                $name = trim((string) ($nameCol ? ($row->{$nameCol} ?? '') : ''));
                $description = trim((string) ($descCol ? ($row->{$descCol} ?? '') : ''));
                $slug = trim((string) ($slugCol ? ($row->{$slugCol} ?? '') : ''));
                $image = trim((string) ($imageCol ? ($row->{$imageCol} ?? '') : ''));

                $name = $name !== '' ? $name : 'Sin nombre';
                $slug = $slug !== '' ? $slug : Str::slug($name);

                return [
                    'id' => $id ?? $slug,
                    'slug' => $slug,
                    'name' => $name,
                    'description' => $description,
                    'image' => $image !== '' ? $image : '/images/categoria-placeholder.webp',
                ];
            })
            ->values();

        return response()->json(['data' => $rows]);
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
