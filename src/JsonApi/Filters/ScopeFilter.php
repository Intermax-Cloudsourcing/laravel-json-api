<?php

namespace Intermax\LaravelApi\JsonApi\Filters;

use Intermax\LaravelOpenApi\Contracts\Filter as OpenApiFilter;
use Spatie\QueryBuilder\AllowedFilter;

class ScopeFilter implements OpenApiFilter, Filter
{
    protected string $fieldName;

    /**
     * @var string[]
     */
    protected array $parameters;

    /**
     * @param  string  $fieldName
     * @param  string[]  $parameters
     */
    public function __construct(string $fieldName, array $parameters = [])
    {
        $this->fieldName = $fieldName;
        $this->parameters = $parameters;
    }

    /**
     * @return string[]
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    public function allowedFilter(): AllowedFilter
    {
        return AllowedFilter::scope($this->fieldName);
    }
}
