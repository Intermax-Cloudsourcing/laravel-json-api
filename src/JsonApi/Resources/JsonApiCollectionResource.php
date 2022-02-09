<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class JsonApiCollectionResource extends ResourceCollection
{
    use IncludesGathering;

    public function __construct($resource, IncludesBag $included = null)
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
     * @param Request $request
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
     * @param mixed $resource
     * @return Collection|mixed
     */
    protected function preparePaginationFields($resource)
    {
        if (! ($resource instanceof LengthAwarePaginator)) {
            return $resource;
        }

        $resource->setPageName('page[number]')
            ->withQueryString();

        $paginated = $resource->toArray();

        $this->with = [
            'links' => [
                'first' => $paginated['first_page_url'],
                'last' => $paginated['last_page_url'],
                'prev' => $paginated['prev_page_url'],
                'next' => $paginated['next_page_url'],
            ],
            'meta' => [
                'currentPage' => $resource->currentPage(),
                'lastPage' => $resource->lastPage(),
                'from' => $paginated['from'],
                'to' => $paginated['to'],
                'total' => $resource->total(),
                'pageSize' => $resource->perPage(),
                'path' => $paginated['path'],
            ],
        ];

        return $resource->getCollection();
    }
}
