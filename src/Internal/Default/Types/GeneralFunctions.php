<?php

declare(strict_types=1);

namespace PHireScript\Internal\Default\Types;

class GeneralFunctions
{
    public static function destroy(mixed $self): void
    {
        unset($self);
        return;
    }

    public static function defined(mixed $self): bool
    {
        return isset($self);
    }

    public static function isEmpty(mixed $self): bool
    {
        return empty($self);
    }

    public static function getClass(mixed $self): string
    {
        $type = \is_object($self) ? $self::class : \gettype($self);
        return $type;
    }

    public static function show(mixed $self): void
    {
        if (\is_array($self) || \is_object($self)) {
            \print_r($self);
        } else {
            echo $self;
        }
        return;
    }

    public function is(mixed $self, string $type): bool
    {
        if (is_object($self) && (class_exists($type) || interface_exists($type))) {
            return $self instanceof $type;
        }

        return match (strtolower($type)) {
            'string'  => \is_string($self),
            'int', 'integer' => \is_int($self),
            'float', 'double' => is_float($self),
            'bool', 'boolean' => is_bool($self),
            'array'   => is_array($self),
            'null'    => is_null($self),
            'object'  => is_object($self),
            'callable' => is_callable($self),
            'resource' => is_resource($self),
            default   => false,
        };
    }
}
