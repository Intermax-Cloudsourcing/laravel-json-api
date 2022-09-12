<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Tests\Utilities;

use Intermax\LaravelJsonApi\Resources\JsonApiResourceCollection;

class UserResourceCollection extends JsonApiResourceCollection
{
    public $collects = UserResource::class;
}
