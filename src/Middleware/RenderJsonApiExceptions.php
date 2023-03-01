<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Middleware;

use Closure;
use Exception;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Intermax\LaravelJsonApi\Exceptions\Handler;

class RenderJsonApiExceptions
{
    public function __construct(
        protected Application $app,
        protected Repository $config,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     *
     * @throws Exception
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->app->bound(ExceptionHandler::class)) {
            throw new Exception(
                message: 'No binding for '.ExceptionHandler::class.'. Laravel Api can only render your exceptions, please register your own error handler.'
            );
        }

        /** @var ExceptionHandler $defaultHandler */
        $defaultHandler = $this->app->make(ExceptionHandler::class);

        $this->app->singleton(ExceptionHandler::class, function (Application $app) use ($defaultHandler) {
            return new Handler($defaultHandler, $this->config);
        });

        return $next($request);
    }
}
