<?php

namespace Intermax\LaravelApi\JsonApi\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Intermax\LaravelApi\JsonApi\Error;
use Intermax\LaravelApi\JsonApi\Resources\ErrorResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler implements ExceptionHandlerContract
{
    public function __construct(
        protected ExceptionHandlerContract $defaultHandler,
        protected Repository $config
    ) {
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $e
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response
    {
        if ($e instanceof ValidationException) {
            return $this->renderValidationException($e, $request);
        }

        if ($e instanceof JsonApiException && $e->hasErrors()) {
            return $this->renderJsonApiException($e, $request);
        }

        if ($e instanceof AuthorizationException) {
            return $this->renderAuthorizationException($e, $request);
        }

        return $this->renderDefaultException($e, $request);
    }

    protected function renderValidationException(ValidationException $e, Request $request): Response
    {
        $errors = new Collection();

        foreach ($e->errors() as $field => $validationErrors) {
            foreach ($validationErrors as $validationError) {
                $errors->add(
                    new Error(
                        status: '422',
                        title: 'Invalid attribute',
                        detail: $validationError,
                        source: ['pointer' => '/'.str_replace('.', '/', $field)]
                    )
                );
            }
        }

        return $this->renderException($request, $errors, 422);
    }

    protected function renderJsonApiException(JsonApiException $e, Request $request): Response
    {
        return $e->toResource()
            ->toResponse($request)
            ->setStatusCode($e->getStatusCode());
    }

    protected function renderAuthorizationException(AuthorizationException $e, Request $request): Response
    {
        return $this->renderException($request, [new Error(
            status: '403',
            title: $e->getMessage(),
        )], 403);
    }

    protected function renderDefaultException(Throwable $e, Request $request): Response
    {
        $response = $this->defaultHandler->render($request, $e);

        $statusCode = (string) $response->getStatusCode();

        $statusText = 'Server Error';

        if (str_starts_with($statusCode, '4')) {
            $statusText = 'Request Error';
        }

        if (method_exists($response, 'statusText')) {
            $statusText = $response->statusText();
        }

        if ($this->config->get('app.debug')) {
            $error = new Error(
                status: $statusCode,
                code: $e->getCode(),
                title: $e::class,
                detail: $e->getMessage(),
                meta: ['trace' => $e->getTrace()]
            );
        } else {
            $error = new Error(
                status: $statusCode,
                title: $statusText,
            );
        }

        return $this->renderException($request, [$error], $statusCode);
    }

    /**
     * @param  iterable<Error>  $errors
     * @return Response
     */
    protected function renderException(Request $request, iterable $errors, int|string $statusCode): Response
    {
        return (new ErrorResourceCollection($errors))
            ->toResponse($request)
            ->setStatusCode((int) $statusCode);
    }

    public function shouldReport(Throwable $e): bool
    {
        return $this->defaultHandler->shouldReport($e);
    }

    public function renderForConsole($output, Throwable $e): void
    {
        $this->defaultHandler->renderForConsole($output, $e);
    }

    public function report(Throwable $e)
    {
        $this->defaultHandler->report($e);
    }
}
