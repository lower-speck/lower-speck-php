<?php

namespace LowerSpeck;

use Closure;

class Make 
{
    private static $bindings = [];

    public static function clear()
    {
        self::$bindings = [];
    }

    public static function bind(string $binding, Closure $closure)
    {
        self::$bindings[$binding] = $closure;
    }

    public static function make(string $binding, array $params = [])
    {
        if (!isset(self::$bindings[$binding])) {
            return new $binding(...$params);
        } else {
            $closure = self::$bindings[$binding];
            return $closure(...$params);
        }
    }
}
