<?php

declare(strict_types=1);

namespace Intermax\LaravelApi\JsonApi\Includes\Contracts;

use Spatie\QueryBuilder\AllowedInclude;

interface Relation
{
    /**
     * Make custom AllowedInclude with AllowedInclude::custom, otherwise use name of the relationship
     *
     * @return AllowedInclude|string
     */
    public function allowedInclude(): AllowedInclude|string;
}
