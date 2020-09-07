<?php

namespace Intermax\LaravelApi\JsonApi\Filters;

use Spatie\QueryBuilder\AllowedFilter;

interface Filter
{
    public function allowedFilter(): AllowedFilter;
}
