<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Intermax\LaravelJsonApi\Error;
use Intermax\LaravelJsonApi\Exceptions\JsonApiException;

/**
 * @property Error $resource
 */
class ErrorResource extends JsonResource
{
    public static $wrap = 'errors';

    public function __construct(Error $resource)
    {
        parent::__construct($resource);
    }

    /**
     * @param  Request  $request
     * @return array<string, string|array<mixed>|MissingValue>
     *
     * @throws JsonApiException
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id ?? new MissingValue,
            'status' => $this->resource->status ?? new MissingValue,
            'code' => $this->resource->code ?? new MissingValue,
            'title' => $this->resource->title ?? new MissingValue,
            'detail' => $this->resource->detail ?? new MissingValue,
            'source' => $this->resource->source ?? new MissingValue,
            'meta' => $this->resource->meta ?? new MissingValue,
            'links' => $this->resource->links ?? new MissingValue,
        ];
    }
}
