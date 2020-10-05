<?php

namespace Intermax\LaravelApi\JsonApi\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Filters\Filter as QueryBuilderFilter;
use Intermax\LaravelOpenApi\Contracts\Filter as OpenApiFilter;
use Illuminate\Support\Arr;

class OperatorFilter implements QueryBuilderFilter, OpenApiFilter, Filter
{
    protected array $operators = [
        'eq' => '=',
        'nq' => '<>',
        'gt' => '>',
        'lt' => '<',
        'gte' => '>=',
        'lte' => '<='
    ];

    protected array $allowedOperators = [];

    protected string $fieldName;

    protected string $type;

    public function __construct(string $fieldName, string $type = 'string', ?array $allowedOperators = null)
    {
        $this->fieldName = $fieldName;
        $this->type = $type;

        if (!$allowedOperators) {
            $this->allowedOperators = array_keys($this->operators);
        } else {
            $this->allowedOperators = $allowedOperators;
        }
    }

    public function __invoke(Builder $query, $value, string $property): void
    {
        if (!is_array($value)) {
            $value = ['eq' => $value];
        }

        foreach ($value as $operator => $filterValue) {
            if (!isset($this->operators[$operator]) || !in_array($operator, $this->allowedOperators)) {
                throw new InvalidFilterQuery(
                    new Collection([$property . '[' . $operator . ']']),
                    new Collection(array_keys($this->parameters()))
                );
            }

            $query->where(Str::snake($property), $this->operators[$operator], $filterValue);
        }
    }

    public function parameters(): array
    {
        $parameters = [];

        foreach ($this->operators as $operator => $value) {
            if (in_array($operator, $this->allowedOperators)) {
                $parameters[sprintf('filter[%s][%s]', $this->fieldName, $operator)] = $this->type;
            }
        }

        return $parameters;
    }

    public function allowedFilter(): AllowedFilter
    {
        return AllowedFilter::custom($this->fieldName, $this);
    }
}
