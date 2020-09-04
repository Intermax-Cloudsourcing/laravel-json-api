<?php

namespace Intermax\LaravelApi\Tests\Resources\Utilities;

use Illuminate\Http\Request;
use Intermax\LaravelApi\JsonApi\Exceptions\JsonApiException;
use Intermax\LaravelApi\JsonApi\Resources\JsonApiCollectionResource;
use Intermax\LaravelApi\JsonApi\Resources\JsonApiResource;
use Intermax\LaravelApi\JsonApi\Resources\RelationType;

class UserCollectionResource extends JsonApiCollectionResource
{
    public $collects = UserResource::class;
}
