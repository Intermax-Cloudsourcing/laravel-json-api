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

    /**
     * @param IncludesBag $included
     */
    public function setIncludesBag($included = null): void
    {
        if ($included && $included instanceof IncludesBag) {
            $this->included = $included;
            return;
        }

        $this->initializeIncludesBag();
    }
}
