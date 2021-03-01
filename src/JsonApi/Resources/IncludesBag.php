<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class IncludesBag implements Arrayable
{
    protected Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    public function add($resource): void
    {
        if ($this->items->search(
            fn ($item) => $resource['id'] == $item['id'] && $resource['type'] == $item['type']
        ) === false) {
            $this->items->add($resource);
        }
    }

    /**
     * @param array|Collection $resources
     */
    public function addMany($resources): void
    {
        foreach ($resources as $resource) {
            $this->add($resource);
        }
    }

    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    public function toArray()
    {
        return $this->items->toArray();
    }
}
