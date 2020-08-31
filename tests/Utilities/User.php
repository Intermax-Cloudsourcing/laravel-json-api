<?php

namespace Intermax\LaravelApi\Tests\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{
    protected $guarded = [];

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function bestFriend(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
