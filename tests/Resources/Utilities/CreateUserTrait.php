<?php

namespace Intermax\LaravelApi\Tests\Resources\Utilities;

use Faker\Factory;
use Illuminate\Support\Carbon;

trait CreateUserTrait
{
    protected $idIncrement = 1;

    protected function createUser()
    {
        $faker = Factory::create();

        $id = $this->idIncrement;

        $this->idIncrement++;

        return new User([
            'id' => $id,
            'email' => $faker->email,
            'password' => password_hash('test', PASSWORD_BCRYPT),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
