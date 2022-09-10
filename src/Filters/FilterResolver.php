<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Intermax\LaravelJsonApi\Includes\Contracts\Relation;
use Intermax\LaravelJsonApi\Requests\FilterRequest;
use Intermax\LaravelJsonApi\Sorts\Contracts\Sort;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class FilterResolver
{
    /**
     * @param  FilterRequest  $request
     * @param  Builder<Model>  $query
     */
    public function resolve(FilterRequest $request, $query): void
    {
        $filters = $this->filters($request);

        $sorts = $this->sorts($request);

        $includes = $this->includes($request);

        $builder = QueryBuilder::for($query);

        if (! empty($filters)) {
            $builder->allowedFilters($filters);
        }

        if (! empty($sorts)) {
            $builder->allowedSorts($sorts);
        }

        if (! empty($includes)) {
            $builder->allowedIncludes($includes);
        }
    }

    /**
     * @param  FilterRequest  $request
     * @return array<AllowedFilter>
     */
    protected function filters(FilterRequest $request): array
    {
        return array_map(fn (Filter $filter) => $filter->allowedFilter(), $request->filters());
    }

    /**
     * @param  FilterRequest  $request
     * @return array<AllowedSort>
     */
    protected function sorts(FilterRequest $request): array
    {
        return array_map(fn (Sort $sort) => $sort->allowedSort(), $request->sorts());
    }

    /**
     * @param  FilterRequest  $request
     * @return array<AllowedInclude|string>
     */
    protected function includes(FilterRequest $request): array
    {
        return array_map(fn (Relation $include) => $include->allowedInclude(), $request->includes());
    }
}
