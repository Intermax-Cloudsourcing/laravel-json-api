<?php

namespace Intermax\LaravelApi\Tests\Resources\JsonApi;

use Faker\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Intermax\LaravelApi\JsonApi\Resources\JsonApiResource;
use Intermax\LaravelApi\Tests\Resources\TestClasses\User;
use Intermax\LaravelApi\Tests\Resources\TestClasses\UserResource;
use Orchestra\Testbench\TestCase;

class ResourceTest extends TestCase
{
    /** @test */
    public function it_transforms_attributes_to_response()
    {
        $user = $this->createUser();

        $resource = new UserResource($user);

        $response = json_decode($resource->toResponse(app('request'))->content());

        $this->assertEquals('users', $response->data->type ?? null);
        $this->assertTrue(isset($response->data->attributes) && !empty($response->data->attributes));
        $this->assertEquals($user->email, $response->data->attributes->email);
    }

    /** @test */
    public function it_transforms_relations_to_json_api()
    {
        $user = $this->createUser();

        $user->setRelation('friends', new Collection([
            $this->createUser(),
            $this->createUser(),
            $this->createUser()
        ]));

        $resource = new UserResource($user);
    }

    protected function createUser()
    {
        $faker = Factory::create();

        return new User([
            'id' => $faker->uuid,
            'email' => $faker->email,
            'password' => password_hash('test', PASSWORD_BCRYPT),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
