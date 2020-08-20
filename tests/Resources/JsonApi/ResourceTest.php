<?php

namespace Intermax\LaravelApi\Tests\Resources\JsonApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Intermax\LaravelApi\JsonApi\Resources\JsonApiResource;
use Intermax\LaravelApi\Tests\Resources\TestModels\User;
use Orchestra\Testbench\TestCase;

class ResourceTest extends TestCase
{
    /** @test */
    public function it_transforms_attributes_to_response()
    {
        $user = new User([
            'id' => 5,
            'email' => 'example@example.com',
            'password' => password_hash('test', PASSWORD_BCRYPT),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $resource = new class($user) extends JsonApiResource {
            public function toArray($request)
            {
                return [
                    'id' => $this->resource->id,
                    'email' => $this->resource->email,
                    'created_at' => $this->resource->created_at,
                    'updated_at' => $this->resource->updated_at
                ];
            }
        };

        $response = json_decode($resource->toResponse(app('request'))->content());

        $this->assertEquals('users', $response->data->type ?? null);
        $this->assertTrue(isset($response->data->attributes) && !empty($response->data->attributes));
        $this->assertEquals($user->email, $response->data->attributes->email);
    }
}
