<?php

declare(strict_types=1);

namespace Intermax\LaravelApi\JsonApi\Filters;

use Illuminate\Database\Eloquent\Builder;
use Intermax\LaravelApi\JsonApi\Requests\FilterRequest;
use Spatie\QueryBuilder\QueryBuilder;

class FilterResolver
{
    /**
     * @param  FilterRequest  $request
     * @param  Builder  $query
     */
    public function resolve(FilterRequest $request, $query): void
    {
        $allowedFilters = [];

        foreach ($request->filters() as $filter) {
            $allowedFilters[] = $filter->allowedFilter();
        }

        QueryBuilder::for($query)->allowedFilters($allowedFilters);
    }
}
