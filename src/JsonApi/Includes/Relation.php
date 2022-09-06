<?php

declare(strict_types=1);

namespace Intermax\LaravelApi\JsonApi\Includes;

class Relation implements Contracts\Relation
{
    public function __construct(
        protected string $name
    ) {
    }

    public function allowedInclude(): string
    {
        return $this->name;
    }
}
