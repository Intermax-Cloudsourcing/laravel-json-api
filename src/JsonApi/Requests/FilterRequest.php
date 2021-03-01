<?php

namespace Intermax\LaravelApi\JsonApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Intermax\LaravelApi\JsonApi\Filters\Filter;
use Intermax\LaravelOpenApi\Contracts\Filter as OpenApiFilter;

abstract class FilterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    /**
     * @return Filter[]|OpenApiFilter[]
     */
    abstract public function filters(): array;
}
