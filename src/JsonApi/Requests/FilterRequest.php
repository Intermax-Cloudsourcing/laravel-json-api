<?php

namespace Intermax\LaravelApi\JsonApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Intermax\LaravelApi\JsonApi\Filters\Filter;
use Intermax\LaravelOpenApi\Contracts\HasQueryParameters;

abstract class FilterRequest extends FormRequest implements HasQueryParameters
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
     * @return Filter[]
     */
    public function filters(): array
    {
        return [];
    }

    public function queryParameters(): array
    {
        $parameters = [];

        foreach ($this->filters() as $filter) {
            $parameters = array_merge($parameters, $filter->parameters());
        }

        return $parameters;
    }
}
