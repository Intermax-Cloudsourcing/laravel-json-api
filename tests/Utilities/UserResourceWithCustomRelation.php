<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Tests\Utilities;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Intermax\LaravelJsonApi\Resources\RelationType;

class UserResourceWithCustomRelation extends UserResource
{
    protected function getRelations(Request $request): array
    {
        return [
            'neighbour' => [
                'type' => RelationType::ONE,
                'links' => [
                    'related' => 'http://example.com/users/'.$this->getId().'/neighbour',
                ],
                'resource' => UserResource::class,
            ],
        ];
    }

    protected function getNeighbourRelationData()
    {
        return new User([
            'id' => 989,
            'email' => 'neighbour@example.com',
            'password' => password_hash('test', PASSWORD_BCRYPT),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
