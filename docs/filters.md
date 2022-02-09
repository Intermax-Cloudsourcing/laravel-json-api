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
