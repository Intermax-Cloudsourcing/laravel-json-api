<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Requests;

use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

abstract class MutationRequest extends FormRequest
{
    abstract protected function type(): string;

    public function authorize(): bool
    {
        if (! $this->hasHeader('Content-Type') || ! str_starts_with((string) $this->header('Content-Type'), 'application/vnd.api+json')) {
            abort(415, 'Invalid content type');
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function rules(Container $app): array
    {
        $rules = [
            'data.id' => ['string'],
            'data.type' => ['required', 'in:'.$this->type(), 'string'],
        ];

        // 'id' is required in JSON:API specification when updating a resource.
        if ($this->method() !== 'POST') {
            $rules['data.id'][] = 'required';
        }

        $rules = $this->mergeIntoRules(
            app: $app,
            rules: $rules,
            methodName: 'attributeRules',
            pathPrefix: 'data.attributes'
        );

        return $this->mergeIntoRules(
            app: $app,
            rules: $rules,
            methodName: 'relationshipRules',
            pathPrefix: 'data.relationships'
        );
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function mergeIntoRules(Container $app, array $rules, string $methodName, string $pathPrefix): array
    {
        $rulesCallable = [$this, $methodName];
        // To allow the container to inject dependencies into the method that's being called
        // we check if it exists through method_exists instead of defining a method to override.
        if (! method_exists($this, $methodName) || ! is_callable($rulesCallable)) {
            return $rules;
        }

        $attributeRules = $app->call($rulesCallable);

        if (! is_array($attributeRules)) {
            throw new Exception(message: __METHOD__.' must return array');
        }

        return array_merge(
            $rules,
            Collection::make($attributeRules)->mapWithKeys(
                fn (string|array $item, string $key) => [$pathPrefix.'.'.$key => $item]
            )->toArray()
        );
    }

    public function validatedAttributes(int|null|string $key, mixed $default = null): mixed
    {
        return $this->validated('data.attributes.'.$key, $default);
    }

    public function validatedRelationships(int|null|string $key, mixed $default = null): mixed
    {
        return $this->validated('data.relationships.'.$key, $default);
    }
}
