<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Resources;

trait IncludesGathering
{
    protected IncludesBag $included;

    public function initializeIncludesBag(): void
    {
        $this->included = new IncludesBag();
    }

    /**
     * @param  IncludesBag  $included
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
