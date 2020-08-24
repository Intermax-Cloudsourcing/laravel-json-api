<?php

namespace Intermax\LaravelApi\Tests\Resources\TestClasses;

use Illuminate\Http\Request;
use Intermax\LaravelApi\JsonApi\Exceptions\JsonApiException;
use Intermax\LaravelApi\JsonApi\Resources\JsonApiResource;

class UserResource extends JsonApiResource
{
    protected function getAttributes(Request $request): array
    {
        return [
            'email' => $this->resource->email,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at
        ];
    }

    protected function getRelations(Request $request): array
    {
        return [];
    }

    /**
     * @param Request $request
     * @return array|string[]
     * @throws JsonApiException
     */
    protected function getLinks(Request $request): array
    {
        return [
            'self' => 'http://example.com/users/' . $this->getId()
        ];
    }
}
