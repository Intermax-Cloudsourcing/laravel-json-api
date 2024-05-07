<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Exceptions;

use Intermax\LaravelJsonApi\Error;
use Intermax\LaravelJsonApi\Exceptions\Contracts\RenderableError;
use Intermax\LaravelJsonApi\Resources\ErrorResource;
use Intermax\LaravelJsonApi\Resources\ErrorResourceCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class JsonApiException extends HttpException implements RenderableError
{
    /**
     * @var iterable|Error[]|null
     */
    protected ?iterable $errors;

    /**
     * @param  iterable<Error>|null  $errors
     */
    public function __construct(
        int $statusCode = 500,
        ?string $message = '',
        ?Throwable $previous = null,
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
