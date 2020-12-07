<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Body;
use Faker\Generator as Faker;

$factory->define(Body::class, function (Faker $faker) {
    return [
        'text' => $faker->text(100),
    ];
});
