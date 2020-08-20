<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

abstract class JsonApiResource extends JsonResource
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ReflectionException
     */
    public function toResponse($request)
    {
        $data = $this->resolve($request);

        $data = $this->transformToJsonApi($data);

        $json = array_merge_recursive(
            [
                static::$wrap => $data
            ],
            $this->with($request),
            $this->additional
        );

        return response()->json($json);
    }

    /**
     * @param $data
     * @return array
     * @throws ReflectionException
     */
    protected function transformToJsonApi($data): array
    {
        $class = new ReflectionClass($this->resource);

        $jsonApiData = [
            'id' => $data['id'],
            'type' => Str::plural(strtolower($class->getShortName())),
            'attributes' => [],
        ];

        foreach ($data as $key => $attribute) {
            if ($key == 'id') {
                continue;
            }

            $jsonApiData['attributes'][$key] = $attribute;
        }

        return $jsonApiData;
    }
}
