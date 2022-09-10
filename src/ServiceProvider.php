<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/query-builder.php',
            'query-builder'
        );
    }
}
