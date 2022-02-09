<?php

namespace Intermax\LaravelApi\Tests\Exceptions;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Intermax\LaravelApi\JsonApi\Error;
use Intermax\LaravelApi\JsonApi\Exceptions\Handler;
use Intermax\LaravelApi\JsonApi\Exceptions\JsonApiException;
use Intermax\LaravelApi\JsonApi\Middleware\RenderJsonApiExceptions;
use Orchestra\Testbench\TestCase;
use Throwable;

class ExceptionHandlerTest extends TestCase
{
    protected Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = \request();
    }

    /**
     * @test
     * @throws BindingResolutionException
     */
    public function it_is_registered_when_middleware_is_added()
    {
        $handler = $this->getHandler();

        $this->assertInstanceOf(Handler::class, $handler);
    }

    /**
     * @test
     * @throws BindingResolutionException
     * @throws Throwable
     */
    public function it_renders_an_exception_in_json_api_format()
    {
        $handler = $this->getHandler();

        $this->assertEquals(
            '{"errors":[{"status":"500","title":"Server Error"}]}',
            $handler->render($this->request, new Exception('Test'))->getContent()
        );
    }

    /**
     * @test
     * @throws BindingResolutionException
     * @throws Throwable
     */
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

    /**
     * @test
     * @throws BindingResolutionException
     * @throws Throwable
     */
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
