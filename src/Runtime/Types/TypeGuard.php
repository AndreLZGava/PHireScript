<?php

namespace PHPScript\Runtime\Types;

class TypeGuard
{
    public static function validateArray(array $data, array $allowedTypes): array
    {
        foreach ($data as $index => $value) {
            $type = gettype($value);
            $mappedType = match ($type) {
                'integer' => 'Int',
                'double'  => 'Float',
                'string'  => 'String',
                'boolean' => 'Bool',
                default   => $type
            };

            if (!in_array($mappedType, $allowedTypes)) {
                throw new \TypeError("Runtime Error: Element at index $index expects [" .
                implode('|', $allowedTypes) . "], but got $mappedType");
            }
        }
        return $data;
    }
}
