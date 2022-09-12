<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Requests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Intermax\LaravelJsonApi\Filters\Contracts\Filter;
use Intermax\LaravelJsonApi\Includes\Contracts\Relation;
use Intermax\LaravelJsonApi\Sorts\Contracts\Sort;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class QueryResolver
{
    /**
     * @template TModelClass of Model
     *
     * @param  CollectionRequest  $request
     * @param  Builder<TModelClass>  $query
     */
    public function resolve(CollectionRequest $request, Builder $query): void
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
     * @param  CollectionRequest  $request
     * @return array<AllowedFilter>
     */
    protected function filters(CollectionRequest $request): array
    {
        return array_map(fn (Filter $filter) => $filter->allowedFilter(), $request->filters());
    }

    /**
     * @param  CollectionRequest  $request
     * @return array<AllowedSort>
     */
    protected function sorts(CollectionRequest $request): array
    {
        return array_map(fn (Sort $sort) => $sort->allowedSort(), $request->sorts());
    }

    /**
     * @param  CollectionRequest  $request
     * @return array<AllowedInclude|string>
     */
    protected function includes(CollectionRequest $request): array
    {
        return array_map(fn (Relation $include) => $include->allowedInclude(), $request->includes());
    }
}
