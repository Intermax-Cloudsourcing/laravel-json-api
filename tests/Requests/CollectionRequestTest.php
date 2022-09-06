<?php

declare(strict_types=1);

namespace Intermax\LaravelApi\Tests\Requests;

use Illuminate\Database\Eloquent\Collection;
use Intermax\LaravelApi\JsonApi\Filters\OperatorFilter;
use Intermax\LaravelApi\JsonApi\Filters\ScopeFilter;
use Intermax\LaravelApi\JsonApi\Includes\Relation;
use Intermax\LaravelApi\JsonApi\Requests\FilterRequest;
use Intermax\LaravelApi\JsonApi\Sorts\Sort;
use Orchestra\Testbench\TestCase;

class CollectionRequestTest extends TestCase
{
    /** @test */
    public function it_outputs_parameters(): void
    {
        $request = new class() extends FilterRequest
        {
            public function filters(): array
            {
                return [
                    new OperatorFilter(
                        fieldName: 'test',
                        allowedOperators: ['eq', 'gt', 'lte'],
                    ),
                    new ScopeFilter(
                        fieldName: 'scope',
                    ),
                ];
            }
        };

        $queryParameters = new Collection($request->queryParameters());

        $this->assertNotNull($queryParameters->where('name', 'filter[test]'));
        $this->assertNotNull($queryParameters->where('name', 'filter[test][eq]'));
        $this->assertNotNull($queryParameters->where('name', 'filter[test][gt]'));
        $this->assertNotNull($queryParameters->where('name', 'filter[test][lte]'));
        $this->assertNotNull($queryParameters->where('name', 'filter[scope]'));
    }

    /** @test */
    public function it_outputs_includes_and_sorts_and_filters_as_parameters()
    {
        $request = new class() extends FilterRequest
        {
            public function includes(): array
            {
                return [
                    new Relation('customers'),
                ];
            }

            public function sorts(): array
            {
                return [
                    new Sort('id'),
                ];
            }

            public function filters(): array
            {
                return [
                    new ScopeFilter('test'),
                ];
            }
        };

        $queryParameters = new Collection($request->queryParameters());

        $this->assertNotNull($queryParameters->where('name', 'filter[test]'));
        $this->assertNotNull($queryParameters->where('name', 'sort'));
        $this->assertNotNull($queryParameters->where('name', 'include'));
    }
}
