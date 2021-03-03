<?php

namespace Intermax\LaravelApi\JsonApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Intermax\LaravelApi\JsonApi\Filters\Filter;

abstract class FilterRequest extends FormRequest
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
    abstract public function filters(): array;
}
