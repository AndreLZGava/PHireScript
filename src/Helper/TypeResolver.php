<?php

declare(strict_types=1);

namespace PHireScript\Helper;

class TypeResolver
{
    private const PRIMITIVES = [
        'String' => 'string',
        'Int'    => 'int',
        'Float'  => 'float',
        'Bool'   => 'bool',
        'Object' => 'object',
        'Array'  => 'array',
        'Void'   => 'void',
        'Null'   => 'null',
        'Mixed'  => 'mixed',
        'Any'    => 'mixed',
        'Queue'  => 'array',
        'List'   => 'array',
        'Stack'  => 'array',
        'Map'    => 'array',
        'Struct' => 'array',
    ];

    private const META_TYPES = [
        'Card', 'Currency', 'Date', 'DateTime', 'Password', 'Phone', 'Time',
    ];

    private const SUPER_TYPES = [
        'Email', 'Ipv4', 'Ipv6', 'Uuid', 'Color', 'Url',
        'CardNumber', 'Cron', 'Cvv', 'Duration', 'ExpiryDate', 'Json', 'Mac', 'Slug',
    ];

    private const META_TYPE_NAMESPACE  = 'PHireScript\\Runtime\\Types\\MetaTypes\\';
    private const SUPER_TYPE_NAMESPACE = 'PHireScript\\Runtime\\Types\\SuperTypes\\';

    public static function isPrimitive(string $type): bool
    {
        return isset(self::PRIMITIVES[$type]);
    }

    public static function isMetaType(string $type): bool
    {
        return \in_array($type, self::META_TYPES, true);
    }

    public static function isSuperType(string $type): bool
    {
        return \in_array($type, self::SUPER_TYPES, true);
    }

    public static function isBuiltIn(string $type): bool
    {
        return self::isPrimitive($type) || self::isMetaType($type) || self::isSuperType($type);
    }

    /**
     * Returns the PHP native type for a PHireScript primitive (e.g. 'Int' → 'int').
     *
     * @throws \InvalidArgumentException when $type is not a primitive
     */
    public static function nativeType(string $type): string
    {
        if (!isset(self::PRIMITIVES[$type])) {
            throw new \InvalidArgumentException("'$type' is not a PHireScript primitive.");
        }
        return self::PRIMITIVES[$type];
    }

    /**
     * Returns the fully-qualified PHP class name for a MetaType or SuperType.
     *
     * @throws \InvalidArgumentException when $type is neither a MetaType nor a SuperType
     */
    public static function fullClassName(string $type): string
    {
        if (self::isMetaType($type)) {
            return self::META_TYPE_NAMESPACE . $type;
        }
        if (self::isSuperType($type)) {
            return self::SUPER_TYPE_NAMESPACE . $type;
        }
        throw new \InvalidArgumentException("'$type' is not a PHireScript MetaType or SuperType.");
    }

    /**
     * Returns the resolved type info array for a known built-in type, or null for
     * custom/unknown types (caller is responsible for resolving those via the symbol table).
     *
     * Return shapes:
     *   primitive → ['category' => 'primitive', 'native' => '<php-type>']
     *   metatype  → ['category' => 'metatype',  'class'  => '<fqcn>']
     *   supertype → ['category' => 'supertype', 'class'  => '<fqcn>']
     *   custom    → null
     *
     * @return array{category: string, native?: string, class?: string}|null
     */
    public static function classify(string $type): ?array
    {
        if (self::isPrimitive($type)) {
            return ['category' => 'primitive', 'native' => self::PRIMITIVES[$type]];
        }
        if (self::isMetaType($type)) {
            return ['category' => 'metatype', 'class' => self::META_TYPE_NAMESPACE . $type];
        }
        if (self::isSuperType($type)) {
            return ['category' => 'supertype', 'class' => self::SUPER_TYPE_NAMESPACE . $type];
        }
        return null;
    }
}
