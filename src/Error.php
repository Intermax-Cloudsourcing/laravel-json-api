<?php

declare(strict_types=1);

namespace Intermax\LaravelJsonApi;

class Error
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $status = null,
        public readonly ?string $code = null,
        public readonly ?string $title = null,
        public readonly ?string $detail = null,
        /**
         * @var array<mixed>|null
         */
        public readonly ?array $source = null,
        /**
         * @var array<mixed>|null
         */
        public readonly ?array $meta = null,
        /**
         * @var array<mixed>|null
         */
        public readonly ?array $links = null,
    ) {}
}
