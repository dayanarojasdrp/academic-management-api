<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApiQuery
{
    public static function paginate(Builder $query, Request $request, int $default = 25)
    {
        $perPage = min(max((int) $request->integer('per_page', $default), 1), 100);

        if ($request->boolean('cursor')) {
            return $query->cursorPaginate($perPage);
        }

        return $query->paginate($perPage);
    }

    public static function applyLike(Builder $query, Request $request, string $parameter, array $columns): void
    {
        $value = trim((string) $request->query($parameter, ''));

        if ($value === '') {
            return;
        }

        $query->where(function (Builder $query) use ($columns, $value): void {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', '%'.$value.'%');
            }
        });
    }

    public static function applyEquals(Builder $query, Request $request, array $filters): void
    {
        foreach ($filters as $parameter => $column) {
            if ($request->filled($parameter)) {
                $query->where($column, $request->query($parameter));
            }
        }
    }
}
