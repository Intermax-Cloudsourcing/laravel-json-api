<?php

namespace Intermax\LaravelApi\Tests\Resources\TestClasses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    protected $guarded = [];

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
