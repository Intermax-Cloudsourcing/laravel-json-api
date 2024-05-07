<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Tests\Requests;

use Illuminate\Database\Eloquent\Collection;
use Intermax\LaravelJsonApi\Filters\OperatorFilter;
use Intermax\LaravelJsonApi\Filters\ScopeFilter;
use Intermax\LaravelJsonApi\Includes\Relation;
use Intermax\LaravelJsonApi\Requests\CollectionRequest;
use Intermax\LaravelJsonApi\Sorts\Sort;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CollectionRequestTest extends TestCase
{
    #[Test]
    public function it_outputs_parameters(): void
    {
        $request = new class() extends CollectionRequest
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

    #[Test]
    public function it_outputs_includes_and_sorts_and_filters_as_parameters()
    {
        $request = new class() extends CollectionRequest
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
