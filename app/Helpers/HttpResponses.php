<?php
namespace App\Helpers;

// @TODO Sampler: search if laravel has something like this
/**
 * Static list of http response codes
 */
final class lpHttpResponses {
    public const SUCCESS = 200;
    public const VALIDATION_FAILED = 400;
    public const UNAUTHORIZED = 401;
    public const NOT_FOUND = 404;
    public const SERVER_ERROR = 500;
}