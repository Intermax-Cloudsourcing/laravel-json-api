<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Exceptions\Contracts;

use Intermax\LaravelJsonApi\Resources\ErrorResource;
use Intermax\LaravelJsonApi\Resources\ErrorResourceCollection;

interface RenderableError
{
    /**
     * @return ErrorResourceCollection<ErrorResource>
     */
    public function toResource(): ErrorResourceCollection;
}
