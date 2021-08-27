<?php

namespace Intermax\LaravelApi\JsonApi\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Intermax\LaravelOpenApi\Contracts\Filter as OpenApiFilter;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Filters\Filter as QueryBuilderFilter;

class OperatorFilter implements QueryBuilderFilter, OpenApiFilter, Filter
{
    /**
     * @var array|string[]
     */
    protected array $operators = [
        'eq' => '=',
        'nq' => '<>',
        'gt' => '>',
        'lt' => '<',
        'gte' => '>=',
        'lte' => '<=',
        'contains' => 'like'
    ];

    /**
     * @var array|string[]
     */
    protected array $allowedOperators = [];

    protected string $fieldName;

    protected ?string $columnName;

    protected string $type;

    /**
     * @param string $fieldName
     * @param string|null $columnName
     * @param string $type
     * @param array|string[]|null $allowedOperators
     */
    public function __construct(
        string $fieldName,
        ?string $columnName = null,
        string $type = 'string',
        ?array $allowedOperators = null
    ) {
        $this->fieldName = $fieldName;
        $this->columnName = $columnName;
        $this->type = $type;

        if (! $allowedOperators) {
            $this->allowedOperators = array_keys($this->operators);
        } else {
            $this->allowedOperators = $allowedOperators;
        }
    }

    /**
     * @param Builder $query
     * @param mixed $value
     * @param string $property
     */
    public function __invoke(Builder $query, $value, string $property): void
    {
        if (! is_array($value)) {
            $value = ['eq' => $value];
        }

        $columnName = $this->columnName ?? Str::snake($property);

        if (! Arr::isAssoc($value)) {
            $query->whereIn($columnName, $value);
        } else {
            foreach ($value as $operator => $filterValue) {
                if (! isset($this->operators[$operator]) || ! in_array($operator, $this->allowedOperators)) {
                    throw new InvalidFilterQuery(
                        new Collection([$property.'['.$operator.']']),
                        new Collection(array_keys($this->parameters()))
                    );
                }

                if ($operator == 'contains') {
                    $filterValue = "%$filterValue%";
                }

                $query->where($columnName, $this->operators[$operator], $filterValue);
            }
        }
    }

    /**
     * @return array|string[]
     */
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
