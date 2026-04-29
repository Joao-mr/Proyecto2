<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesIndexFilters
{
    protected function applyIndexFilters(
        Builder $query,
        array $allowedOrderColumns = ['id', 'name', 'created_at'],
        string $searchTitleColumn = 'name',
        string $defaultOrderColumn = 'created_at',
        string $defaultOrderDirection = 'desc'
    ): Builder {
        $orderColumn = request('order_column', $defaultOrderColumn);
        if (!in_array($orderColumn, $allowedOrderColumns, true)) {
            $orderColumn = $defaultOrderColumn;
        }

        $orderDirection = request('order_direction', $defaultOrderDirection);
        if (!in_array($orderDirection, ['asc', 'desc'], true)) {
            $orderDirection = $defaultOrderDirection;
        }

        return $query
            ->when(request('search_id'), function (Builder $query, $searchId) {
                $query->where('id', $searchId);
            })
            ->when(request('search_title'), function (Builder $query, $searchTitle) use ($searchTitleColumn) {
                $query->where($searchTitleColumn, 'like', '%' . $searchTitle . '%');
            })
            ->when(request('search_global'), function (Builder $query, $searchGlobal) use ($searchTitleColumn) {
                $query->where(function (Builder $nestedQuery) use ($searchGlobal, $searchTitleColumn) {
                    $nestedQuery->where('id', $searchGlobal)
                        ->orWhere($searchTitleColumn, 'like', '%' . $searchGlobal . '%');
                });
            })
            ->orderBy($orderColumn, $orderDirection);
    }
}