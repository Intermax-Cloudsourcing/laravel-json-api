<?php

namespace Intermax\LaravelApi\Tests\Resources\JsonApi;

use Faker\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Intermax\LaravelApi\JsonApi\Resources\JsonApiResource;
use Intermax\LaravelApi\Tests\Utilities\CreateUserTrait;
use Intermax\LaravelApi\Tests\Utilities\User;
use Intermax\LaravelApi\Tests\Utilities\UserResource;
use Orchestra\Testbench\TestCase;

class ResourceTest extends TestCase
{
    use CreateUserTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idIncrement = 1;
    }

    /** @test */
    public function it_outputs_attributes()
    {
        $user = $this->createUser();

        $resource = new UserResource($user);

        $response = json_decode($resource->toResponse(app('request'))->content());

        $this->assertEquals('users', $response->data->type ?? null);
        $this->assertTrue(isset($response->data->attributes) && !empty($response->data->attributes));
        $this->assertEquals($user->email, $response->data->attributes->email);
    }

    /** @test */
    public function it_outputs_relations_with_includes()
    {
        $user = $this->createUser();

        $user->setRelation('friends', new Collection([
            $this->createUser(),
            $this->createUser(),
            $this->createUser()
        ]));

        $user->setRelation('bestFriend', $this->createUser());

        $resource = new UserResource($user);

        $response = json_decode($resource->toResponse(app('request'))->content());

        $this->assertTrue(isset($response->data->relationships));

        $friends = $response->data->relationships->friends ?? null;
        $bestFriend = $response->data->relationships->bestFriend ?? null;

        $this->assertTrue($friends && isset($friends->data) && isset($friends->data[0]));

        $this->assertTrue($bestFriend && $bestFriend->data->type === 'users');
    }

    /** @test */
    public function it_outputs_relations_with_links_and_without_includes()
    {
        $user = $this->createUser();

        $resource = new UserResource($user);

        $response = json_decode($resource->toResponse(app('request'))->content());

        $this->assertTrue(isset($response->data->relationships));
        $this->assertTrue(isset($response->data->relationships->friends->links->related));

        $this->assertFalse(isset($response->data->friends->data));
        $this->assertFalse(isset($response->includes));
    }
}
