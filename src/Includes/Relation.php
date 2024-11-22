<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Includes;

use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\Includes\IncludedRelationship;

class Relation implements Contracts\Relation
{
    public function __construct(
        protected string $name,
        protected ?string $alias = null,
    ) {}

    public function allowedInclude(): AllowedInclude
    {
        return new AllowedInclude(
            name: $this->alias ?: $this->name,
            includeClass: new IncludedRelationship,
            internalName: $this->name,
        );
    }
}
