<?php

namespace Intermax\LaravelApi\JsonApi\Resources;

use Illuminate\Pagination\AbstractPaginator;

trait IncludesGathering
{
    protected IncludesBag $included;

    public function initializeIncludesBag()
    {
        $this->included = new IncludesBag();
    }

    public function setIncludesBag(?IncludesBag $included = null): void
    {
        if (!$included) {
            $this->initializeIncludesBag();
            return;
        }

        $this->included = $included;
    }
}
