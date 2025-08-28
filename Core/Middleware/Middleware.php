<?php

namespace Core\Middleware;
// use Core\Middleware\Auth;
// use Core\Middleware\Guest;

class Middleware
{

    public const MAP = [
        'guest' => Guest::class,
        'auth' => Auth::class
    ];

    public static function resolve($key)
    {
        if (!$key)
        {
            return;
        }

        $middleware = static::MAP[$key] ?? false;

        if (!$middleware)
        {
            throw new \Exception("Middleware not found for the key '{$key}'.");
        }

        (new $middleware)->handle();
    }
}