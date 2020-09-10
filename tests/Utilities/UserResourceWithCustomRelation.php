<?php

namespace Intermax\LaravelApi\Tests\Utilities;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Intermax\LaravelApi\JsonApi\Resources\RelationType;

class UserResourceWithCustomRelation extends UserResource
{
    protected function getRelations(Request $request): array
    {
        return [
            'neighbour' => [
                'type' => RelationType::ONE,
                'links' => [
                    'related' => 'http://example.com/users/' . $this->getId() . '/neighbour'
                ],
                'resource' => UserResource::class
            ]
        ];
    }

    protected function getNeighbourRelationData()
    {
        return new User([
            'id' => 989,
            'email' => 'neighbour@example.com',
            'password' => password_hash('test', PASSWORD_BCRYPT),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
