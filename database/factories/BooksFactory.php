<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Books;
use Faker\Generator as Faker;

$factory->define(Books::class, function (Faker $faker) {
    /*
    - title (string with max length of 255 characters) [OK]
    - isbn (10 digits, select from an array of valid isbn provided below) [OK]
    - published_at (Date in format YYYY-MM-DD) [OK]
    - status (enum [‘CHECKED_OUT’,’AVAILABLE’]) [OK]
    */

    $arrValidIsbn = [
        '0005534186',
        '0978110196',
        '0978108248',
        '0978194527',
        '0978194004',
        '0978194985',
        '0978171349',
        '0978039912',
        '0978031644',
        '0978168968',
        '0978179633',
        '0978006232',
        '0978195248',
        '0978125029',
        '0978078691',
        '0978152476',
        '0978153871',
        '0978125010',
        '0593139135',
        '0441013597',
        '1564161447',
        '0970001606',
        '1501286323',
        '0890831386',
        '0890831777',
        '0890831831',
        '0890831971',
        '0890832323',
        '0890832617',
        '0890833133',
        '1456498541',
        '1594569940',
        '1421830493',
    ];

    return [
        "title"        => $faker->sentence(rand(2, 5)),
        "isbn"         => $faker->unique()->randomElement($arrValidIsbn),
        // "published_at" => $faker->date_between('-20 years', '-1 month'),
        "published_at" => $faker->dateTimeBetween('-100 years', '-1 day'),
        "status"       => $faker->randomElement(['CHECKED_OUT', 'AVAILABLE']),
    ];
});
