<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Tests\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Intermax\LaravelJsonApi\Error;
use Intermax\LaravelJsonApi\Exceptions\Handler;
use Intermax\LaravelJsonApi\Exceptions\JsonApiException;
use Intermax\LaravelJsonApi\Middleware\RenderJsonApiExceptions;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionHandlerTest extends TestCase
{
    protected Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = \request();
    }

    #[Test]
    public function it_is_registered_when_middleware_is_added()
    {
        $handler = $this->getHandler();

        $this->assertInstanceOf(Handler::class, $handler);
    }

    #[Test]
    public function it_renders_an_exception_in_json_api_format()
    {
        $handler = $this->getHandler();

        $response = $handler->render($this->request, new Exception('Test'));

        $this->assertEquals(500, $response->getStatusCode());

        $this->assertEquals(
            '{"errors":[{"status":"500","title":"Internal Server Error"}]}',
            $response->getContent()
        );
    }

    #[Test]
    public function it_respects_the_status_code_of_the_exception()
    {
        $response = $this->getHandler()->render($this->request, new HttpException(419));

        $this->assertEquals(
            419,
            $response->getStatusCode()
        );
    }

    #[Test]
    public function it_renders_an_authorization_exception()
    {
        $response = $this->getHandler()->render(
            $this->request,
            new AuthorizationException('My authorization exception.'),
        );

        $content = json_decode($response->getContent());

        $this->assertEquals(403, $response->getStatusCode());

        $this->assertEquals('403', $content->errors[0]->status);
        $this->assertEquals('My authorization exception.', $content->errors[0]->title);
    }

    #[Test]
    public function it_renders_a_validation_exception_in_json_api_format()
    {
        $handler = $this->getHandler();

        /** @var Factory $validationFactory */
        $validationFactory = app('validator');

        try {
            $validationFactory->make([
                'data' => [
                    'type' => 'test',
                ],
            ], [
                'data.type' => ['in:exceptions'],
            ])->validate();
        } catch (ValidationException $e) {
            $this->assertEquals(
                '{"errors":[{"status":"422","title":"Invalid attribute","detail":"The selected data.type is invalid.","source":{"pointer":"\/data\/type"}}]}',
                $handler->render($this->request, $e)->getContent()
            );
        }
    }

    #[Test]
    public function it_renders_a_json_api_exception_in_json_api_format()
    {
        $handler = $this->getHandler();

        $exception = new JsonApiException(
            statusCode: 400,
            errors: [
                new Error(
                    title: 'You did something wrong!',
                    detail: 'Catastrophic.'
                ),
            ]
        );

        $response = $handler->render($this->request, $exception);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(
            '{"errors":[{"title":"You did something wrong!","detail":"Catastrophic."}]}',
            $response->getContent()
        );
    }

    /**
     * @return mixed
     *
     * @throws BindingResolutionException
     * @throws Exception
     */
    protected function getHandler(): ExceptionHandler
    {
        /** @var RenderJsonApiExceptions $middleware */
        $middleware = app()->make(RenderJsonApiExceptions::class);

        $middleware->handle($this->request, fn () => response('test'));

        return app()->make(ExceptionHandler::class);
    }
}
