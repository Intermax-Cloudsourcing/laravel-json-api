<?php

namespace Intermax\LaravelApi\JsonApi\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Support\Arr;

class OperatorFilter implements Filter, \Intermax\LaravelOpenApi\Contracts\Filter
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
            $operator = 'eq';
        } else {
            $operator = array_key_first($value);

            if (!isset($this->operators[$operator]) || !in_array($operator, $this->allowedOperators)) {
                throw new InvalidFilterQuery(
                    new Collection([$property . '[' . $operator . ']']),
                    new Collection(array_keys($this->parameters()))
                );
            }
        }

        $query->where($property, $this->operators[$operator], Arr::first($value));
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
}
