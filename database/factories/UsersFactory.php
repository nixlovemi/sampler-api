<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Users;
use Faker\Generator as Faker;

$factory->define(Users::class, function (Faker $faker) {
    /*
    - email (string with max length of 255 characters) [OK]
    - name (string with max length of 255 characters) [OK]
    - password (min 8 characters, min 1 capital letter, 1 number) [OK]
    - date_of_birth (Date in format YYYY-MM-DD) [OK]
    */
    return [
        "email"         => $faker->unique()->userName . '@gmail.com',
        "name"          => $faker->name,
        "password"      => bcrypt('verdaumsdrobs'),
        "date_of_birth" => $faker->dateTime,
    ];
});
