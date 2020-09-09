<?php

namespace Intermax\LaravelApi\Tests\Filters;

use Intermax\LaravelApi\JsonApi\Filters\OperatorFilter;
use Intermax\LaravelApi\Tests\Utilities\User;
use Orchestra\Testbench\TestCase;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OperatorFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('query-builder', [
            'parameters' => [
                'include' => 'include',
                'filter' => 'filter',
                'sort' => 'sort',
                'fields' => 'fields',
                'append' => 'append'
            ],
            'count_suffix' => 'Count'
        ]);
    }

    /** @test */
    public function it_produces_a_greater_than_query()
    {
        request()->query->set('filter', [
            'id' => ['gt' => 2]
        ]);

        $query = $this->createQuery('id');

        $this->assertEquals('select * from `users` where `id` > ?', $query->toSql());
        $this->assertEquals(2, $query->getBindings()[0]);
    }

    /** @test */
    public function it_produces_an_equals_query()
    {
        request()->query->set('filter', [
            'id' => ['eq' => 3]
        ]);

        $query = $this->createQuery('id');

        $this->assertEquals('select * from `users` where `id` = ?', $query->toSql());
        $this->assertEquals(3, $query->getBindings()[0]);
    }

    /** @test */
    public function it_produces_a_greater_than_or_equals_query()
    {
        request()->query->set('filter', [
            'id' => ['gte' => 3]
        ]);

        $query = $this->createQuery('id');

        $this->assertEquals('select * from `users` where `id` >= ?', $query->toSql());
        $this->assertEquals(3, $query->getBindings()[0]);
    }

    /** @test */
    public function it_produces_a_lesser_than_or_equals_query()
    {
        request()->query->set('filter', [
            'id' => ['lte' => 3]
        ]);

        $query = $this->createQuery('id');

        $this->assertEquals('select * from `users` where `id` <= ?', $query->toSql());
        $this->assertEquals(3, $query->getBindings()[0]);
    }

    /** @test */
    public function it_produces_a_lesser_than_query()
    {
        request()->query->set('filter', [
            'id' => ['lt' => 3]
        ]);

        $query = $this->createQuery('id');

        $this->assertEquals('select * from `users` where `id` < ?', $query->toSql());
        $this->assertEquals(3, $query->getBindings()[0]);
    }

    /** @test */
    public function it_produces_a_not_equal_query()
    {
        request()->query->set('filter', [
            'id' => ['nq' => 3]
        ]);

        $query = $this->createQuery('id');

        $this->assertEquals('select * from `users` where `id` <> ?', $query->toSql());
        $this->assertEquals(3, $query->getBindings()[0]);
    }

    protected function createQuery($field)
    {
        return QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::custom($field, new OperatorFilter($field))
            ]);
    }
}
