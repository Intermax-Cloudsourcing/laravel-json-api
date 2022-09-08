<?php

declare(strict_types=1);

namespace Intermax\LaravelApi\JsonApi\Filters;

use Intermax\LaravelOpenApi\Contracts\QueryParameter;
use Spatie\QueryBuilder\AllowedFilter;

interface Filter
{
    public function allowedFilter(): AllowedFilter;

    /**
     * @return array<QueryParameter>
     */
    public function parameters(): array;
}
