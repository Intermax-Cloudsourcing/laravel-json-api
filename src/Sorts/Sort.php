<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Sorts;

use Illuminate\Support\Str;
use Intermax\LaravelOpenApi\Generator\Parameters\QueryParameter;
use Spatie\QueryBuilder\AllowedSort;

class Sort implements Contracts\Sort
{
    public function __construct(
        protected string $name,
        protected ?string $example = null,
        protected ?string $internalName = null,
    ) {}

    public function allowedSort(): AllowedSort
    {
        return AllowedSort::field($this->name, $this->internalName ?? Str::snake($this->name));
    }

    public function parameters(): array
    {
        return [
            new QueryParameter(
                name: 'sort',
                example: $this->example,
            ),
        ];
    }
}
