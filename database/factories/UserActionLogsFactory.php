<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\UserActionLogs;
use Faker\Generator as Faker;
use App\Models\Books;
use App\Models\Users;

$factory->define(UserActionLogs::class, function (Faker $faker) {
    return [
        /*
        - book_id (integer)
        - user_id (integer)
        - action (enum ['CHECKIN', 'CHECKOUT'])
        - created_at (timestamp)
        */
        
        "book_id"    => function() {
            return Books::all()->random();
        },
        "user_id"    => function() {
            return Users::all()->random();
        },
        "action"     => $faker->randomElement(['CHECKIN', 'CHECKOUT']),
        "created_at" => $faker->dateTimeBetween('-2 years', '-1 day'),
    ];
});
