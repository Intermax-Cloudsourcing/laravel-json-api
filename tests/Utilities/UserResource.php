<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Tests\Utilities;

use Illuminate\Http\Request;
use Intermax\LaravelJsonApi\Exceptions\JsonApiException;
use Intermax\LaravelJsonApi\Resources\JsonApiResource;
use Intermax\LaravelJsonApi\Resources\RelationType;

class UserResource extends JsonApiResource
{
    protected function getAttributes(Request $request): array
    {
        return [
            'email' => $this->resource->email,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    /**
     * @throws JsonApiException
     */
    protected function getRelations(Request $request): array
    {
        return [
            'friends' => [
                'type' => RelationType::MANY,
                'links' => [
                    'related' => 'http://example.com/users/'.$this->getId().'/friends',
                ],
                'resource' => UserResourceCollection::class,
            ],
            'bestFriend' => [
                'type' => RelationType::ONE,
                'links' => [
                    'related' => 'http://example.com/users/'.$this->getId().'/best-friend',
                ],
                'resource' => self::class,
            ],
        ];
    }

    /**
     * @return array|string[]
     *
     * @throws JsonApiException
     */
    protected function getLinks(Request $request): array
    {
        return [
            'self' => 'http://example.com/users/'.$this->getId(),
        ];
    }
}
