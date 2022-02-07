<?php

namespace Intermax\LaravelApi\JsonApi\Exceptions\Contracts;

use Intermax\LaravelApi\JsonApi\Resources\ErrorResource;
use Intermax\LaravelApi\JsonApi\Resources\ErrorResourceCollection;

interface RenderableError
{
    /**
     * @return ErrorResourceCollection<ErrorResource>
     */
    public function toResource(): ErrorResourceCollection;
}
