# Laravel API

Laravel API is a package to quickly build an API according to the JSON:API specification.

- [Installation](#installation)
- [Configuration](#configuration)
- [Basic usage](#basic-usage)
- [Filters](#filters)
    * [FilterRequest](#filterrequest)
    * [Controller](#controller)
    * [Filter types](#filter-types)
    * [Custom filters](#custom-filters)

## Installation

```bash
composer require intermax/laravel-json-api
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

## Filters

Most APIs have ways to apply filters to your requests. With JSON:API this is done by applying a `filter` query string to the url:

```
/users?filter[isAdmin]=true
```

### FilterRequest
With Laravel API you can configure some predefined filters or add your own. To do this you can add a `FilterRequest` to your controller method. Essentially this is an extended [FormRequest](https://laravel.com/docs/8.x/validation#form-request-validation). Your custom `FilterRequest` needs to extend the `Intermax\LaravelApi\JsonApi\Requests\FilterRequest`. It might look like this:

```php 
namespace App\Http\Requests;

use App\Http\Filters\ScopeFilter;
use Intermax\LaravelApi\JsonApi\Filters\OperatorFilter;
use Intermax\LaravelApi\JsonApi\Requests\FilterRequest;

class UserCollectionRequest extends FilterRequest
{
    public function filters(): array
    {
        return [
            new OperatorFilter('createdAt'),
            new ScopeFilter('isAdmin'),
        ];
    }
}
```

This specific `FilterRequest` adds two filters, `filter[createdAt]` and `filter[isAdmin]`. To see how these specific filters work, see [filter types](#filter-types).

### Controller
To make the filters actually do their magic, we need a little more. In the controller the `FilterResolver` needs to be used to apply the filters to the Eloquent query:

```php
use Intermax\LaravelApi\JsonApi\Filters\FilterResolver;

class UserController
{
    public function index(UserCollectionRequest $request, FilterResolver $filterResolver): UserResourceCollection
    {
        $query = User::query();
        
        $filterResolver->resolve($request, $query);
        
        $query->orderBy(...) // You can alter the query more if needed
        
        return new UserResourceCollection($query->jsonPaginate());
    }
}
```

### Filter types

### Custom filters
