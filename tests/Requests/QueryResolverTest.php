<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Tests\Requests;

use Intermax\LaravelJsonApi\Filters\OperatorFilter;
use Intermax\LaravelJsonApi\Includes\Relation;
use Intermax\LaravelJsonApi\Requests\CollectionRequest;
use Intermax\LaravelJsonApi\Requests\QueryResolver;
use Intermax\LaravelJsonApi\ServiceProvider;
use Intermax\LaravelJsonApi\Sorts\Sort;
use Intermax\LaravelJsonApi\Tests\Utilities\User;
use Orchestra\Testbench\TestCase;

class QueryResolverTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    /** @test */
    public function it_adds_includes_to_the_query(): void
    {
        $request = new class() extends CollectionRequest
        {
            public function includes(): array
            {
                return [
                    new Relation('friends'),
                ];
            }
        };

        request()->replace(['include' => 'friends']);

        /** @var QueryResolver $queryResolver */
        $queryResolver = $this->app->make(QueryResolver::class);

        $query = User::query();
        $queryResolver->resolve($request, $query);

        $this->assertNotNull($query->getEagerLoads()['friends'] ?? null);
    }

    /** @test */
    public function it_adds_a_sort_to_the_query(): void
    {
        $request = new class() extends CollectionRequest
        {
            public function sorts(): array
            {
                return [
                    new Sort('name'),
                ];
            }
        };

        request()->replace(['sort' => 'name']);

        /** @var QueryResolver $queryResolver */
        $queryResolver = $this->app->make(QueryResolver::class);

        $query = User::query();
        $queryResolver->resolve($request, $query);

        $this->assertStringContainsString('order by `name` asc', $query->toSql());
    }

    /** @test */
    public function it_adds_a_filter_to_the_query(): void
    {
        $request = new class() extends CollectionRequest
        {
            public function filters(): array
            {
                return [
                    new OperatorFilter('name'),
                ];
            }
        };

        request()->replace(['filter' => ['name' => 'Test']]);

        /** @var QueryResolver $queryResolver */
        $queryResolver = $this->app->make(QueryResolver::class);

        $query = User::query();
        $queryResolver->resolve($request, $query);

        $this->assertStringContainsString('where `name` = ?', $query->toSql());
        $this->assertContains('Test', $query->getBindings());
    }
}
