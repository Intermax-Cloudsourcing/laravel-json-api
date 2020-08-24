<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use Intermax\LaravelApi\JsonApi\Exceptions\JsonApiException;

abstract class JsonApiResource extends JsonResource
{
    protected array $included = [];

    private bool $resourceIsEloquent = false;

    public function __construct($resource)
    {
        if ($resource instanceof Model) {
            $this->resourceIsEloquent = true;
        }

        parent::__construct($resource);
    }

    /**
     * @param Request $request
     * @return array
     * @throws JsonApiException
     * @throws ReflectionException
     */
    final public function toArray($request)
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'attributes' => $this->getAttributes($request),
            'relationships' => $this->discoverRelations($request) ?? new MissingValue(),
            'links' => $this->getLinks($request)
        ];
    }

    public function with($request)
    {
        $parentWith = parent::with($request);

        if (empty($this->included)) {
            return $parentWith;
        }

        return array_merge_recursive(
            $parentWith,
            [
                'included' => array_unique($this->included, SORT_REGULAR)
            ]
        );
    }

    /**
     * Expects an associative array of the attributes you want to include in the response, excluding id
     *
     * @param Request $request
     * @return array
     */
    abstract protected function getAttributes(Request $request): array;

    /**
     * Expects an array of relation names as keys, and if it's a collection or object as the value. The resource will
     * automatically try to derive relation data (and possibly includes) from $this->resource. Example:
     * [
     *     'comments' => [
     *         'type' => RelationType::MANY,
     *         'links' => ['related' => route(...)]
     *     ],
     *     'author' => [
     *         'type' => RelationType::ONE,
     *         'links' => ...
     *     ]
     * ]
     *
     * @param $request
     * @return array
     */
    abstract protected function getRelations(Request $request): array;

    /**
     * Expects an associative array of links for the resource, preferably use the route helper to generate links (eg:
     * ['self' => route('articles.show', ['article' => 1])] )
     *
     * @param $request
     * @return array
     */
    abstract protected function getLinks(Request $request): array;

    /**
     * @return string
     * @throws JsonApiException
     */
    protected function getId(): string
    {
        if (!isset($this->resource->id)) {
            throw new JsonApiException('No id found, did you forget to implement ' . __CLASS__ . '::getId()?');
        }

        return $this->resource->id;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    protected function getType(): string
    {
        $class = new ReflectionClass($this->resource);

        return Str::plural(strtolower($class->getShortName()));
    }

    protected function discoverRelations(Request $request): ?array
    {
        $definedRelations = $this->getRelations($request);

        if (empty($definedRelations)) {
            return null;
        }

        $relations = [];

        foreach ($definedRelations as $definedRelation => $values) {
            $relation = [];

            if (isset($values['links'])) {
                $relation = [
                    'links' => $values['links']
                ];
            }

            if (
                ($this->resourceIsEloquent && $this->resource->relationLoaded($definedRelation))
                || (!$this->resourceIsEloquent && isset($this->resource->$definedRelation))
            ) {
                $resourceClass = $values['resource'];

                /** @var JsonApiCollectionResource $resource */
                $resource = new $resourceClass($this->resource->$definedRelation);

                $resolvedResource = $resource->resolve();

                if ($values['type'] === RelationType::MANY) {
                    $this->included = array_merge($this->included, $resolvedResource);

                    $relation['data'] = array_map(
                        fn($element) => Arr::only($element, ['type', 'id']),
                        $resolvedResource
                    );
                } else {
                    $this->included[] = $resolvedResource;
                    $relation['data'] = Arr::only($resolvedResource, ['type', 'id']);
                }
            }

            if (!isset($relation['links'])) {
                $relation['meta'] = [
                    'hasLinks' => false
                ];
            }

            $relations[$definedRelation] = $relation;
        }

        return $relations;
    }
}
