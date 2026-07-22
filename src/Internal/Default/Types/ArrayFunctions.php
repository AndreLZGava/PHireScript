<?php

declare(strict_types=1);

namespace PHireScript\Internal\Default\Types;

class ArrayFunctions
{
    public static function contains(mixed $searching, array $self): bool
    {
        return \in_array($searching, $self);
    }

    public static function add(array $self, string $key, mixed $value): array
    {
        $self[$key] = $value;
        return $self;
    }

    public static function addEnd(array $self, array $params): void
    {
        \array_push($self, $params);
        return;
    }

    public static function addStart(array $self, array $params): void
    {
        \array_unshift($self, $params);
        return;
    }

    public static function last(array $self): mixed
    {
        return empty($self) ? null : $self[\array_key_last($self)];
    }

    public static function first(array $self): mixed
    {
        return \current($self ?? []);
    }

    public static function remove(array $self, mixed $key): array
    {
        unset($self[$key]);
        return $self;
    }

    public static function removeValue(array $self, mixed $value): array
    {
        $self = array_filter($self, fn($v) => $v !== $value);
        return $self;
    }

    public static function length(array $self): int
    {
        return \count($self);
    }

    public static function isEmpty(array $self): bool
    {
        return empty($self);
    }

    public static function map(callable $callback, array $self): array
    {
        return \array_map($callback, $self);
    }

    public static function filter(callable $callback, array $self): array
    {
        return \array_filter($self, $callback);
    }

    public static function reduce(callable $callback, array $self, mixed $initial): array
    {
        return \array_reduce($self, $callback, $initial);
    }

    public static function find(array $self, callable $callback): mixed
    {
        return \current(\array_filter($self, $callback));
    }
}
