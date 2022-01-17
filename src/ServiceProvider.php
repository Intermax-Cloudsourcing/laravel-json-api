<?php

namespace Intermax\LaravelApi;

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
