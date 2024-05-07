<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi\Tests\Requests;

use Intermax\LaravelJsonApi\Requests\MutationRequest;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MutationRequestTest extends TestCase
{
    #[Test]
    public function it_merges_attributes_and_relationship_rules_into_json_api_rules(): void
    {
        $request = new class() extends MutationRequest
        {
            public function type(): string
            {
                return 'cars';
            }

            public function attributeRules(): array
            {
                return [
                    'title' => ['required', 'string'],
                ];
            }

            public function relationshipRules(): array
            {
                return [
                    'brand' => [],
                    'brand.data' => [],
                    'brand.data.type' => ['string', 'in:brands'],
                    'brand.data.id' => ['string'],
                ];
            }
        };

        $rules = app()->call([$request, 'rules']);

        $this->assertEquals([
            'data.id' => [
                'string',
                'required',
            ],
            'data.type' => [
                'required',
                'in:cars',
                'string',
            ],
            'data.attributes.title' => [
                'required',
                'string',
            ],
            'data.relationships.brand' => [],
            'data.relationships.brand.data' => [],
            'data.relationships.brand.data.type' => [
                'string',
                'in:brands',
            ],
            'data.relationships.brand.data.id' => [
                'string',
            ],
        ], $rules);
    }

    #[Test]
    public function it_rejects_the_request_if_the_json_api_content_type_is_not_used(): void
    {
        $formRequest = new class() extends MutationRequest
        {
            public function type(): string
            {
                return 'cars';
            }
        };

        $formRequest->setMethod('POST');
        $formRequest->headers->set('Content-Type', 'text/html');

        $this->expectException(HttpException::class);

        try {
            $formRequest->authorize();
        } catch (HttpException $e) {
            $this->assertEquals(415, $e->getStatusCode());

            throw $e;
        }
    }
}
