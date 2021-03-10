<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Intermax\LaravelApi\JsonApi\Exceptions\JsonApiException;
use ReflectionClass;
use ReflectionException;

abstract class JsonApiResource extends JsonResource
{
    use IncludesGathering;

    private bool $resourceIsEloquent = false;

    /**
     * @param mixed $resource
     * @param ?IncludesBag $included
     */
    public function __construct($resource, $included = null)
    {
        if ($resource instanceof Model) {
            $this->resourceIsEloquent = true;
        }

        $this->setIncludesBag($included);

        parent::__construct($resource);
    }

    /**
     * @param Request $request
     * @return array<mixed>
     * @throws JsonApiException
     * @throws ReflectionException
     */
    final public function toArray($request): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'attributes' => $this->getAttributes($request),
            'relationships' => $this->discoverRelations($request) ?? new MissingValue(),
            'links' => $this->getLinks($request),
        ];
    }

    /**
     * @param Request $request
     * @return array<mixed>
     */
    public function with($request): array
    {
        $parentWith = parent::with($request);

        if ($this->included->isEmpty()) {
            return $parentWith;
        }

        return array_merge_recursive(
            $parentWith,
            [
                'included' => $this->included->toArray(),
            ]
        );
    }

    /**
     * Expects an associative array of the attributes you want to include in the response, excluding id.
     *
     * @param Request $request
     * @return array<mixed>
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
     * ].
     *
     * @param Request $request
     * @return array<mixed>
     */
    abstract protected function getRelations(Request $request): array;

    /**
     * Expects an associative array of links for the resource, preferably use the route helper to generate links (eg:
     * ['self' => route('articles.show', ['article' => 1])] ).
     *
     * @param Request $request
     * @return array<mixed>
     */
    abstract protected function getLinks(Request $request): array;

    /**
     * @return string
     * @throws JsonApiException
     */
    protected function getId(): string
    {
        if (! isset($this->resource->id)) {
            throw new JsonApiException('No id found, did you forget to implement '.__METHOD__.'?');
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

        return Str::plural(Str::camel($class->getShortName()));
    }

    /**
     * @param Request $request
     * @return array<mixed>|null
     */
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
                    'links' => $values['links'],
                ];
            }

            $relationData = $this->getRelationData((string) $definedRelation);

            if ($relationData) {
                $resourceClass = $values['resource'];

                /** @var JsonApiCollectionResource<mixed>|JsonApiResource $resource */
                $resource = new $resourceClass($relationData, $this->included);

                $resolvedResource = $resource->resolve();

                if ($values['type'] === RelationType::MANY) {
                    $this->included->addMany($resolvedResource);

                    $relation['data'] = array_map(
                        fn ($element) => Arr::only($element, ['type', 'id']),
                        $resolvedResource
                    );
                } else {
                    $this->included->add($resolvedResource);
                    $relation['data'] = Arr::only($resolvedResource, ['type', 'id']);
                }
            }

            if (! isset($relation['links'])) {
                $relation['meta'] = [
                    'hasLinks' => false,
                ];
            }

            $relations[$definedRelation] = $relation;
        }

        return $relations;
    }

    /**
     * @param string $definedRelation
     * @return mixed
     */
    protected function getRelationData(string $definedRelation)
    {
        $methodName = 'get'.ucfirst($definedRelation).'RelationData';

        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }
        if (method_exists($this->resource, $methodName)) {
            return $this->resource->$methodName();
        }

        if (
            ($this->resourceIsEloquent && $this->resource->relationLoaded($definedRelation))
            || (! $this->resourceIsEloquent && isset($this->resource->$definedRelation))
        ) {
            return $this->resource->$definedRelation;
        }

        return null;
    }
}
