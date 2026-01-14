<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter\Type;

use PHPScript\Compiler\Emitter\UseRegistry;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;

class PhpTypeResolver
{
    public function resolve(PropertyDefinition $prop, UseRegistry $uses): string
    {
        $types = [];

        foreach ($prop->resolvedTypeInfo as $info) {
            match ($info['category']) {
                'primitive' => $types[] = $info['native'],

                'supertype' => $types[] = 'string',

                'metatype', 'custom' => $types[] = $prop->type,

                default => null,
            };

            if (isset($info['class'])) {
                $uses->add($info['class']);
            }
        }

        if ($this->allowsNull($prop)) {
            $types[] = 'null';
        }

        return implode('|', array_unique($types));
    }

    public function assignment(PropertyDefinition $prop): string
    {
        $types = $prop->resolvedTypeInfo;
        $explicitTypes =  explode('|', $this->phpType($prop));
        $itemsToVerify = in_array('null', $explicitTypes, true) ?
        count($types) - 1 :
        count($types);
        $var = $prop->name;
        if ($itemsToVerify > 1) {
            $this->uses[] = \PHPScript\Runtime\Types\UnionType::class;

            $typeClasses = [];
            foreach ($types as $t) {
                if (isset($t['class'])) {
                    $this->uses[] = $t['class'];
                    $className = (new \ReflectionClass($t['class']))->getShortName();
                    $typeClasses[] = "$className::class";
                }
            }

            $classList = implode(', ', $typeClasses);
            return "\$this->$var = UnionType::cast(\$$var, [$classList]);";
        }

        $typeInfo = $types[0];
        return match ($typeInfo['category']) {
            'supertype' => "\$this->$var = {$prop->type}::cast(\$$var);",
            'metatype'  => "\$this->$var = \$$var instanceof {$prop->type} ? \$$var : new {$prop->type}(\$$var);",
            default     => "\$this->$var = \$$var;"
        };
    }

    public function phpType(PropertyDefinition $prop): string
    {
        $types = [];

        foreach ($prop->resolvedTypeInfo as $info) {
            if ($info['category'] === 'primitive') {
                $types[] = $info['native'];
            }

            if ($info['category'] === 'supertype') {
                $types[] = 'string';
            }

            if (in_array($info['category'], ['metatype', 'custom'], true)) {
                $types[] = $prop->type;
            }

            if (($info['name'] ?? null) === 'Null') {
                $types[] = 'null';
            }
        }

        return implode('|', array_unique($types));
    }

    private function allowsNull(PropertyDefinition $prop): bool
    {
        foreach ($prop->resolvedTypeInfo as $info) {
            if (($info['name'] ?? null) === 'Null') {
                return true;
            }
        }
        return false;
    }
}
