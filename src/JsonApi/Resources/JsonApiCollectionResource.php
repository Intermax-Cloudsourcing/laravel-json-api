<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

class JsonApiCollectionResource extends ResourceCollection
{
    public function __construct($resource)
    {
        $resource = $this->preparePaginationFields($resource);

        parent::__construct($resource);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function with($request)
    {
        return array_merge_recursive(
            parent::with($request),
            [
                'links' => [
                    'self' => $request->url()
                ]
            ]
        );
    }

    /**
     * @param $resource
     * @return Collection|mixed
     */
    protected function preparePaginationFields($resource)
    {
        if (!($resource instanceof LengthAwarePaginator)) {
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
                'next' => $paginated['next_page_url']
            ],
            'meta' => [
                'currentPage' => $resource->currentPage(),
                'lastPage' => $resource->lastPage(),
                'from' => $paginated['from'],
                'to' => $paginated['to'],
                'total' => $resource->total(),
                'pageSize' => $resource->perPage(),
                'path' => $paginated['path']
            ]
        ];

        return $resource->getCollection();
    }
}
