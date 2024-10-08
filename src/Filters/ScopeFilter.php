<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Filters;

use Intermax\LaravelJsonApi\Filters\Contracts\Filter;
use Intermax\LaravelOpenApi\Generator\Parameters\QueryParameter;
use Spatie\QueryBuilder\AllowedFilter;

class ScopeFilter implements Filter
{
    /**
     * @param  array<int, string>|null  $options
     */
    public function __construct(
        protected string $fieldName,
        protected string $type = 'string',
        protected ?array $options = null,
        protected mixed $example = null,
        protected ?string $scopeName = null,
    ) {}

    /**
     * @return array<QueryParameter>
     */
    public function parameters(): array
    {
        return [
            new QueryParameter(
                name: 'filter['.$this->fieldName.']',
                type: $this->type,
                options: $this->options,
                example: $this->example,
            ),
        ];
    }

    public function allowedFilter(): AllowedFilter
    {
        return AllowedFilter::scope(
            name: $this->fieldName,
            internalName: $this->scopeName,
        );
    }
}
