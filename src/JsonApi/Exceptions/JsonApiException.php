<?php

declare(strict_types=1);

namespace Intermax\LaravelApi\JsonApi\Exceptions;

use Intermax\LaravelApi\JsonApi\Error;
use Intermax\LaravelApi\JsonApi\Exceptions\Contracts\RenderableError;
use Intermax\LaravelApi\JsonApi\Resources\ErrorResource;
use Intermax\LaravelApi\JsonApi\Resources\ErrorResourceCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class JsonApiException extends HttpException implements RenderableError
{
    /**
     * @var iterable|Error[]|null
     */
    protected ?iterable $errors;

    /**
     * @param  int  $statusCode
     * @param  string|null  $message
     * @param  Throwable|null  $previous
     * @param  int|null  $code
     * @param  iterable|Error[]|null  $errors
     */
    public function __construct(
        int $statusCode = 500,
        ?string $message = '',
        Throwable $previous = null,
        ?int $code = 0,
        ?iterable $errors = null,
    ) {
        parent::__construct($statusCode, (string) $message, $previous, [], (int) $code);

        $this->errors = $errors;
    }

    public function hasErrors(): bool
    {
        return ! is_null($this->errors);
    }

    /**
     * @return ErrorResourceCollection<ErrorResource>
     */
    public function toResource(): ErrorResourceCollection
    {
        return new ErrorResourceCollection($this->errors);
    }
}
