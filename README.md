# Laravel JSON:API

Laravel API is a package to quickly build an API according to the JSON:API specification. It also generates OpenAPI documentation on the fly based on your API endpoints.

- [Installation](#installation)
- [Configuration](#configuration)
- [Basic usage](#basic-usage)
- [Query Parameters](#query-parameters)
    * [CollectionRequest](#collectionrequest)
    * [Controller](#controller)
    * [Filter types](#filter-types)
- [MutationRequest](#mutation-requests)
- [OpenAPI generation](#openapi-generation)

## Installation

```bash
composer require intermax/laravel-json-api
```

## Configuration

To render exceptions in JSON:API format, you can add the middleware `Intermax\LaravelJsonApi\Middleware\RenderJsonApiExceptions` to applicable routes. A sensible example would be in the HTTP Kernel in the API middleware group:

```php
// app/Http/Kernel.php

use Intermax\LaravelJsonApi\Middleware\RenderJsonApiExceptions;
...
    protected $middlewareGroups = [
        ...
    
        'api' => [
            ...
            RenderJsonApiExceptions::class,
        ],
    ];
```

## Basic Usage

To create an endpoint you just create a Laravel route like you're used to doing. After that you need to create a resource. Let's assume we make an endpoint that returns a `UserResource`:

```php
use Illuminate\Http\Request;
use Intermax\LaravelJsonApi\Resources\JsonApiResource;

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


> ❗ **TIP**: Make sure to type hint your resource on the controller method so Open API docs can be generated correctly. Read more about this at [Open API generation](open-api-generation.md)

## Query Parameters

You might want ways to apply filters, sorts and includes to your requests. With JSON:API this is done by applying a `filter`, `sort` and include variables to the query string:

```
/users?filter[isAdmin]=true&sort=name&include=team
```

### CollectionRequest
With this package you can configure some predefined filters or add your own. You can also add includes and sorts. To do this you can add a `CollectionRequest` to your controller method. Essentially this is an extended [FormRequest](https://laravel.com/docs/8.x/validation#form-request-validation). Your custom `CollectionRequest` needs to extend the `Intermax\LaravelJsonApi\Requests\CollectionRequest`. It might look like this:

```php 
namespace App\Http\Requests;

use Intermax\LaravelJsonApi\Filters\ScopeFilter;
use Intermax\LaravelJsonApi\Filters\OperatorFilter;
use Intermax\LaravelJsonApi\Requests\CollectionRequest;
use Intermax\LaravelJsonApi\Sorts\Sort;
use Intermax\LaravelJsonApi\Includes\Relation;

class UserCollectionRequest extends CollectionRequest
{
    public function filters(): array
    {
        return [
            new OperatorFilter('createdAt'),
            new ScopeFilter('isAdmin'),
        ];
    }
    
    public function sorts(): array
    {
        return [
            new Sort('name'),
        ];
    }
    
    public function includes(): array
    {
        return [
            new Relation('team'), // Eloquent relation name
        ];
    }
}
```

This specific `CollectionRequest` adds two filters, `filter[createdAt]` and `filter[isAdmin]`. To see how these specific filters work, see [filter types](#filter-types).

### Controller
To make the filters, includes and sorts actually do their magic, we need a little more. In the controller the `QueryResolver` needs to be used to apply the filters to the Eloquent query. Under the hood this uses the [laravel-query-builder](https://github.com/spatie/laravel-query-builder) package from spatie.

```php
use Intermax\LaravelJsonApi\Requests\QueryResolver;

class UserController
{
    public function index(UserCollectionRequest $request, QueryResolver $queryResolver): UserResourceCollection
    {
        $query = User::query();
        
        $queryResolver->resolve($request, $query);
        
        $query->where(...) // You can alter the query further if needed
        
        return new UserResourceCollection($query->jsonPaginate());
    }
}
```

### Filter Types

This package provides two types of filters out of the box. One is the `ScopeFilter`. Like its name implies this will call the scope with the value that's being sent.

The second one is called the `OperatorFilter`. It allows you to query with a set of operators:

- Equals: `filter[column]=value` or `filter[column][eq]=value`
- Not equals: `filter[column][nq]=value`
- Greater than: `filter[column][gt]=value`
- Less than: `filter[column][lt]=value`
- Greater than or equals: `filter[column][gte]=value`
- Less than or equals: `filter[column][lte]=value`
- Contains: `filter[column][contains]=value`

Allowed operators can be specified (default all are allowed):

```php
use Intermax\LaravelJsonApi\Filters\OperatorFilter

new OperatorFilter(
    fieldName: 'name',
    allowedOperators: [
        'eq',
        'nq',
        'contains',
    ], 
);
```

## Mutation Requests
For POST, PUT or PATCH requests this package provides an extendable base request for convenience. Instead of a regular `FormRequest` you know from Laravel it should extend the `MutationRequest`. 
This class helps you to adhere to the JSON:API specification for your requests:
- It rejects requests without the correct content type (`application/vnd.api+json`)
- The implementing class can provide rules for the `attributes` and `relationships` fields
- It has methods to retrieve `validatedAttributes` and `validatedRelationships`

> ⚠️ **WARNING**: Be aware that if you were previously not checking for the content type you could easily create a breaking change in your application. 

### Usage
An example implementation may look like this:

```php
use Intermax\LaravelJsonApi\Requests\MutationRequest;

class UserUpdateRequest extends MutationRequest
{
    protected function type(): string
    {
        return 'users';
    }
    
    protected function attributeRules(): array
    {
        return [
            'email' => ['email'],
            'name' => ['string'],
        ]
    }
}
```

And in the controller you can use the methods to retrieve validated fields:

```php
public function update(UserUpdateRequest $request, User $user): UserResource
{
    $user->update($request->validatedAttributes());
    
    return new UserResource($user);
}
```


## OpenAPI Generation

This package leverages the [Laravel Open API package](https://github.com/Intermax-Cloudsourcing/laravel-open-api) to provide a `/docs` endpoint (and `/docs/json` and `/docs/yaml` endpoints).

The open API package will scan for api routes, read FormRequests, determine ApiResources and try to guess the output of the resource. We aim to generate as much documentation as possible with a minimal amount of configuration.

There are a couple of things that need to be in place for this to work best:

- Use the CollectionRequest for 'collection-type' endpoints, even if you don't use Eloquent it will still be able to infer the query parameters. You can even validate query parameters with rules.
- Typehint the resource you want to return in your controller method.
- Use FormRequest validation for your POST/PUT/PATCH endpoints and include all fields in it (even if they have no validation). The package uses this to determine the request body.
- The package will get all your fields/attributes from the resource array.

### Improving resource attribute data types

If you look at the docs and see all your resource attributes are listed as string in the array, there is one more thing you can do to improve it. Wrap every field in a `Intermax\LaravelOpenApi\Generator\Values\Value` type object:

```php
use Intermax\LaravelOpenApi\Generator\Values\StringValue;
use Intermax\LaravelOpenApi\Generator\Values\IntegerValue;
use Intermax\LaravelOpenApi\Generator\Values\NumberValue;
use Intermax\LaravelOpenApi\Generator\Values\DateTimeValue;
use Intermax\LaravelOpenApi\Generator\Values\BooleanValue;
use Carbon\Carbon;
use Intermax\LaravelJsonApi\Resources\JsonApiResource;

class UserResource extends JsonApiResource
{
    public function getAttributes(Request $request): array
    {
        return [
            'age' => new IntegerValue(fn () => Carbon::now()->diffInYears($this->resource->birthDate)),
            'email' => new StringValue(fn () => $this->resource->email),
            'name' => new StringValue(fn () => $this->resource->name),
            'createdAt' => new DateTimeValue(fn () => $this->resource->created_at),
            'isAdmin' => new BooleanValue(fn () => $this->resource->is_admin),
        ];
    }
}
```

