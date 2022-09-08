<?php

declare(strict_types=1);

namespace Intermax\LaravelApi\Tests\Utilities;

use Intermax\LaravelApi\JsonApi\Resources\JsonApiCollectionResource;

class UserCollectionResource extends JsonApiCollectionResource
{
    public $collects = UserResource::class;
}
