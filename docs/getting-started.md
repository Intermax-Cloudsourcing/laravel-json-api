## Installation

Make sure you add the Intermax repository to your project's `composer.json`:

```json
"repositories": {
    "intermax": {
        "type": "composer",
        "url": "https://development.intermax-pages.nl/tools/composer-repository/"
    }
},
```
Then install:

```bash
composer require intermax/laravel-api
```

## Configuration

To render exceptions in JSON:API format, you can add the middleware `Intermax\LaravelApi\JsonApi\Middleware\RenderJsonApiExceptions` to applicable routes. A sensible example would be in the HTTP Kernel in the API middleware group:

```php
// app/Http/Kernel.php

use Intermax\LaravelApi\JsonApi\Middleware\RenderJsonApiExceptions;
...
    protected $middlewareGroups = [
        ...
    
        'api' => [
            ...
            RenderJsonApiExceptions::class,
        ],
    ];
```

## Basic usage

To create an endpoint you just create a Laravel route like you're used to doing. After that you need to create a resource. Let's assume we make an endpoint that returns a `UserResource`:

```php
use Illuminate\Http\Request;
use Intermax\LaravelApi\JsonApi\Resources\JsonApiResource;

class UserResource extends JsonApiResource
{
    public function getAttributes(Request $request): array
    {
        return [
            'email' => $this->resource->email,
            'name' => $this->resource->name,
        ];
    }
    
    public function getRelations(Request $request): ?array
    {
        return null;
    }
    
    public function getLinks(Request $request): ?array
    {
        return null;
    }
}
```

After that you should create your controller method. Generally you would want an API Resource Controller. If you return the resource you just made your first endpoint with the Laravel API package:

```php
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController
{
    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }
}
```

::: tip 
Make sure to type hint your resource on the controller method so Open API docs can be generated correctly. Read more about this on the [Open API generation page](open-api-generation.md) 
:::
