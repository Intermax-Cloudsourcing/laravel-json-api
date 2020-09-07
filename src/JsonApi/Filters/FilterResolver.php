<?php

namespace Intermax\LaravelApi\JsonApi\Filters;

use Intermax\LaravelApi\JsonApi\Requests\FilterRequest;
use Spatie\QueryBuilder\QueryBuilder;

class FilterResolver
{
    public function resolve(FilterRequest $request, $query): void
    {
        $allowedFilters = [];

        foreach ($request->filters() as $filter) {
            $allowedFilters[] = $filter->allowedFilter();
        }

        QueryBuilder::for($query)->allowedFilters($allowedFilters);
    }
}
