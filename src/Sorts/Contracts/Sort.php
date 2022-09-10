<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Sorts\Contracts;

use Intermax\LaravelOpenApi\Contracts\QueryParameter;
use Spatie\QueryBuilder\AllowedSort;

interface Sort
{
    public function allowedSort(): AllowedSort;

    /**
     * @return array<QueryParameter>
     */
    public function parameters(): array;
}
