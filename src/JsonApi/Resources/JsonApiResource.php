<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Database\Eloquent\Model;
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
     * @return array
     * @throws ReflectionException
     */
    final public function toArray($request)
    {
        $data = $this->data($request);

        $data = $this->transformToJsonApi($data);

        return $data;
    }

    abstract public function data($request);

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
