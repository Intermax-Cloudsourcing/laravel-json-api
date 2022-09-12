<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Intermax\LaravelJsonApi\Filters\Filter;
use Intermax\LaravelJsonApi\Includes\Contracts\Relation;
use Intermax\LaravelJsonApi\Sorts\Contracts\Sort;
use Intermax\LaravelOpenApi\Contracts\HasQueryParameters;
use Intermax\LaravelOpenApi\Contracts\QueryParameter;

abstract class CollectionRequest extends FormRequest implements HasQueryParameters
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @return array<Filter>
     */
    public function filters(): array
    {
        return [];
    }

    /**
     * @return array<Relation>
     */
    public function includes(): array
    {
        return [];
    }

    /**
     * @return array<Sort>
     */
    public function sorts(): array
    {
        return [];
    }

    public function queryParameters(): array
    {
        $parameters = [];

        $items = array_merge($this->filters(), $this->sorts());

        foreach ($items as $item) {
            $parameters = array_merge($parameters, $item->parameters());
        }

        return array_merge($parameters, $this->getIncludeQueryParameters());
    }

    /**
     * @return array<QueryParameter>
     */
    private function getIncludeQueryParameters(): array
    {
        if (! empty($this->includes())) {
            return [new \Intermax\LaravelOpenApi\Generator\Parameters\QueryParameter(
                name: 'include',
            )];
        }

        return [];
    }
}
