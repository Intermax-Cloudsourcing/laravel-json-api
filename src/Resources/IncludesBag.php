<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * @implements  Arrayable<int, mixed>
 */
class IncludesBag implements Arrayable
{
    /**
     * @var Collection<int, mixed>
     */
    protected Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    /**
     * @param  array<mixed>  $resource
     * @return void
     */
    public function add(array $resource): void
    {
        if ($this->items->search(
            fn ($item) => $resource['id'] == $item['id'] && $resource['type'] == $item['type']
        ) === false) {
            $this->items->add($resource);
        }
    }

    /**
     * @param  array<array<mixed>>  $resources
     */
    public function addMany(array $resources): void
    {
        foreach ($resources as $resource) {
            $this->add($resource);
        }
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return $this->items->toArray();
    }
}
