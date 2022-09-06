<?php

declare(strict_types=1);

namespace Intermax\LaravelApi\Tests\Requests;

use Intermax\LaravelApi\JsonApi\Filters\FilterResolver;
use Intermax\LaravelApi\JsonApi\Filters\OperatorFilter;
use Intermax\LaravelApi\JsonApi\Includes\Relation;
use Intermax\LaravelApi\JsonApi\Requests\FilterRequest;
use Intermax\LaravelApi\JsonApi\Sorts\Sort;
use Intermax\LaravelApi\ServiceProvider;
use Intermax\LaravelApi\Tests\Utilities\User;
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
        $request = new class() extends FilterRequest
        {
            public function includes(): array
            {
                return [
                    new Relation('friends'),
                ];
            }
        };

        request()->replace(['include' => 'friends']);

        /** @var FilterResolver $queryResolver */
        $queryResolver = $this->app->make(FilterResolver::class);

        $query = User::query();
        $queryResolver->resolve($request, $query);

        $this->assertNotNull($query->getEagerLoads()['friends'] ?? null);
    }

    /** @test */
    public function it_adds_a_sort_to_the_query(): void
    {
        $request = new class() extends FilterRequest
        {
            public function sorts(): array
            {
                return [
                    new Sort('name'),
                ];
            }
        };

        request()->replace(['sort' => 'name']);

        /** @var FilterResolver $queryResolver */
        $queryResolver = $this->app->make(FilterResolver::class);

        $query = User::query();
        $queryResolver->resolve($request, $query);

        $this->assertStringContainsString('order by `name` asc', $query->toSql());
    }

    /** @test */
    public function it_adds_a_filter_to_the_query(): void
    {
        $request = new class() extends FilterRequest
        {
            public function filters(): array
            {
                return [
                    new OperatorFilter('name'),
                ];
            }
        };

        request()->replace(['filter' => ['name' => 'Test']]);

        /** @var FilterResolver $queryResolver */
        $queryResolver = $this->app->make(FilterResolver::class);

        $query = User::query();
        $queryResolver->resolve($request, $query);

        $this->assertStringContainsString('where `name` = ?', $query->toSql());
        $this->assertContains('Test', $query->getBindings());
    }
}
