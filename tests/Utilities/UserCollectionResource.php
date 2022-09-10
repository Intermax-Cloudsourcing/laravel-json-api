<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Tests\Utilities;

use Intermax\LaravelJsonApi\Resources\JsonApiCollectionResource;

class UserCollectionResource extends JsonApiCollectionResource
{
    public $collects = UserResource::class;
}
