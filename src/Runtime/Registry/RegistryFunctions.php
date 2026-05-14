<?php

declare(strict_types=1);

namespace PHireScript\Runtime\Registry;

class FunctionRegistry
{
    private static $functions = [];

    public static function registerFunction($name, $callable)
    {
        self::$functions[$name] = $callable;
    }

    public static function getFunction($name)
    {
        return self::$functions[$name] ?? null;
    }

    public static function listFunctions()
    {
        return array_keys(self::$functions);
    }
}
