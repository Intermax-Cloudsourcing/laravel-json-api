# Laravel API

Laravel API is a package to quickly build an API according to the JSON:API specification.

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
## Getting started

To create an endpoint you just create a Laravel route like you're used to doing. After that you need to create a resource. Let's assume we make a `show()` endpoint.

```php
use Illuminate\Http\Request;
use Intermax\LaravelApi\JsonApi\Resources\JsonApiResource;

class UserResource extends JsonApiResource
{
    public function getAttributes(Request $request) : array
    {
        return [
            'email' => $this->resource->email,
            'name' => $this->resource->name,
        ];
    }
    
    public function getRelations(Request $request) : ?array
    {
        return null;
    }
    
    public function getLinks(Request $request) : ?array
    {
        return null;
    }
}
```

After that you should create your controller method. Generally you would want an API Resource Controller. If you return the resource you just made your first endpoint with the Laravel API package.
