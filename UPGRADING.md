# Upgrading

## From 0.X to 1.0

### Package Namespace Change
*Likelihood of impact: high*

- Package namespace was changed from `Intermax\LaravelApi` to `Intermax\LaravelJsonApi`.
- All classes under `Intermax\LaravelApi\JsonApi` were moved up a level.

Change all usages accordingly. For example, the usage of `Intermax\LaravelApi\JsonApi\Resources\JsonApiResource` changed to `Intermax\LaravelJsonApi\Resources\JsonApiResource`.

### `FilterRequest` name change
*Likelihood of impact: high*

`FilterRequest` has now received the more appropriate `CollectionRequest` as its name.

### `JsonApiCollectionResource` name change
*Likelihood of impact: high*

`JsonApiCollectionResource` is now named `JsonApiResourceCollection` to make it more consistent with Laravel naming.

### `FilterResolver` name change
*Likelihood of impact: medium*

If you automatically apply filters to your Eloquent query:`FilterResolver` is now renamed to `QueryResolver` because it also applies sorts and includes to the query builder instance.

### `Filter` interface namespace change
*Likelihood of impact: low*

When using custom filters that implement the `Filter` contract: the `Filter` *FQN* is now `Intermax\LaravelJsonApi\Filters\Contracts\Filter`.
