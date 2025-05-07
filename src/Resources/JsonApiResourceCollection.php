<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class JsonApiResourceCollection extends ResourceCollection
{
    use IncludesGathering;

    public function __construct($resource, ?IncludesBag $included = null)
    {
        $resource = $this->preparePaginationFields($resource);
        $this->setIncludesBag($included);

        parent::__construct($resource);
    }

    protected function collectResource($resource)
    {
        $resource = parent::collectResource($resource);

        $collection = $resource;

        if ($resource instanceof AbstractPaginator) {
            $collection = $resource->getCollection();
        }

        assert($collection instanceof Collection);

        $collection->each(fn ($item) => $item->setIncludesBag($this->included));

        return $resource;
    }

    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function with($request)
    {
        $array = array_merge_recursive(
            parent::with($request),
            [
                'links' => [
                    'self' => $request->fullUrl(),
                ],
            ]
        );

        if (! $this->included->isEmpty()) {
            $array['included'] = $this->included->toArray();
        }

        return $array;
    }

    /**
     * @param  mixed  $resource
     * @return Collection|mixed
     */
    protected function preparePaginationFields($resource)
    {
        if (! ($resource instanceof LengthAwarePaginator)) {
            return $resource;
        }

        $resource->setPageName('page[number]')
            ->withQueryString();

        $this->with = [
            'links' => [
                'first' => $resource->url(1),
                'last' => $resource->url($resource->lastPage()),
                'prev' => $resource->previousPageUrl(),
                'next' => $resource->nextPageUrl(),
            ],
            'meta' => [
                'currentPage' => $resource->currentPage(),
                'lastPage' => $resource->lastPage(),
                'from' => $resource->firstItem(),
                'to' => $resource->lastItem(),
                'total' => $resource->total(),
                'pageSize' => $resource->perPage(),
                'path' => $resource->path(),
            ],
        ];

        return $resource->getCollection();
    }
}
