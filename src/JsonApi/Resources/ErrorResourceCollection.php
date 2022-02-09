<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ErrorResourceCollection extends ResourceCollection
{
    public static $wrap = 'errors';

    public $collects = ErrorResource::class;

    public function toResponse($request): JsonResponse
    {
        return parent::toResponse($request)
            ->header('Content-Type', 'application/vnd.api+json');
    }
}
