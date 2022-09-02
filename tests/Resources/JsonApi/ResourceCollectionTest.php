<?php

namespace Intermax\LaravelApi\Tests\Resources\JsonApi;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Intermax\LaravelApi\Tests\Utilities\CreateUserTrait;
use Intermax\LaravelApi\Tests\Utilities\UserCollectionResource;
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
            $this->createUser(),
        ]);

        $paginator = $this->createPaginator($collection);

        $resource = new UserCollectionResource($paginator);

        $response = json_decode($resource->toResponse(app('request'))->content());

        $this->assertTrue(count($response->data) === 5);
        $this->assertTrue($response->data[0]->type === 'users');

        $this->assertEquals('http://example.com/users?'.urlencode('page[number]').'=1', $response->links->first);

        $this->assertFalse(isset($response->included));
    }

    /** @test */
    public function it_outputs_a_collection_with_nested_includes()
    {
        $topUser = $this->createUser();
        $userWithoutFriends = $this->createUser();
        $friendOfTopUser = $this->createUser();

        $friendOfTopUserFriend = $this->createUser();

        $topUser->setRelation('friends', [$friendOfTopUser]);
        $friendOfTopUser->setRelation('bestFriend', $friendOfTopUserFriend);

        $collection = new Collection([
            $topUser,
            $userWithoutFriends,
        ]);

        $paginator = $this->createPaginator($collection);

        $resource = new UserCollectionResource($paginator);

        $response = json_decode($resource->toResponse(app('request'))->content());

        $this->assertTrue(isset($response->included));

        $isFound = false;
        foreach ($response->included as $include) {
            $isFound = $include->type == 'users' && $include->id == $friendOfTopUserFriend->id;
            if ($isFound) {
                break;
            }
        }

        $this->assertTrue($isFound);
    }

    /**
     * @param  Collection  $collection
     * @return LengthAwarePaginator
     */
    protected function createPaginator(Collection $collection): LengthAwarePaginator
    {
        $paginator = new LengthAwarePaginator($collection, $collection->count(), 15);
        $paginator->setPath('http://example.com/users');

        return $paginator;
    }
}
