<?php

namespace Intermax\LaravelApi\JsonApi\Filters;

use Intermax\LaravelOpenApi\Contracts\Filter as OpenApiFilter;
use Spatie\QueryBuilder\AllowedFilter;

class ScopeFilter implements OpenApiFilter, Filter
{
    protected string $fieldName;

    protected array $parameters;

    public function __construct(string $fieldName, array $parameters = [])
    {
        $this->fieldName = $fieldName;
        $this->parameters = $parameters;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function allowedFilter(): AllowedFilter
    {
        return AllowedFilter::scope($this->fieldName);
    }
}
