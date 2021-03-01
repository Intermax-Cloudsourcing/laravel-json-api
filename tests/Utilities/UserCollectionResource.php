<?php

namespace Intermax\LaravelApi\Tests\Utilities;

use Intermax\LaravelApi\JsonApi\Resources\JsonApiCollectionResource;

class UserCollectionResource extends JsonApiCollectionResource
{
    public $collects = UserResource::class;
}
