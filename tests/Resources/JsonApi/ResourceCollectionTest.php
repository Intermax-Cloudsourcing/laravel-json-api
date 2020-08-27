<?php

namespace Intermax\LaravelApi\Tests\Resources\JsonApi;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Intermax\LaravelApi\Tests\Resources\Utilities\CreateUserTrait;
use Intermax\LaravelApi\Tests\Resources\Utilities\UserCollectionResource;
use Orchestra\Testbench\TestCase;

class ResourceCollectionTest extends TestCase
{
    use CreateUserTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->idIncrement = 1;
    }

    /** @test */
    public function it_outputs_a_collection_with_pagination()
    {
        $collection = new Collection([
            $this->createUser(),
            $this->createUser(),
            $this->createUser(),
            $this->createUser(),
            $this->createUser()
        ]);

        $paginator = new LengthAwarePaginator($collection, $collection->count(), 15);
        $paginator->setPath('http://example.com/users');

        $resource = new UserCollectionResource($paginator);

        $response = json_decode($resource->toResponse(app('request'))->content());

        $this->assertTrue(count($response->data) === 5);
        $this->assertTrue($response->data[0]->type === 'users');

        $this->assertEquals('http://example.com/users?' . urlencode('page[number]') . '=1', $response->links->first);
    }
}
