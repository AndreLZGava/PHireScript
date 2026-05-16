<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Base\Type;

use PHireScript\Compiler\Emitter\Base\UseRegistry;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Helper\TypeResolver;
use PHireScript\Runtime\Types\UnionType;

class PhpTypeResolver
{
    private readonly array $uses;
    public function resolve(PropertyNode $prop, UseRegistry $uses): string
    {
        $types = [];

        foreach ($prop->resolvedTypeInfo as $info) {
            match ($info['category']) {
                // 'null' primitive is handled exclusively by allowsNull() at the end
                'primitive' => $info['native'] !== 'null' ? ($types[] = $info['native']) : null,

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

        return \implode('|', \array_unique($types));
    }

    public function assignment(PropertyNode $prop, UseRegistry $uses): string
    {
        $types = $prop->resolvedTypeInfo;
        $explicitTypes =  \explode('|', $this->phpType($prop));
        $itemsToVerify = \in_array('null', $explicitTypes, true) ?
            \count($types) - 1 :
            \count($types);
        $var = $prop->name;
        if ($itemsToVerify > 1) {
            $uses->add(UnionType::class);

            $typeClasses = [];
            foreach ($types as $t) {
                if (isset($t['class'])) {
                    $uses->add($t['class']);
                    $className = (new \ReflectionClass($t['class']))->getShortName();
                    $typeClasses[] = "$className::class";
                }
            }
            $classList = \implode(', ', $typeClasses);
            return "\$this->$var = UnionType::cast(\$$var, [$classList]);";
        }

        $typeInfo = $types[0];
        if (isset($typeInfo['class'])) {
            $uses->add($typeInfo['class']);
        }
        $type = $prop->type ?? null;
        if (!isset($prop->type)) {
            $type = \implode('|', $prop->types);
        }

        return match ($typeInfo['category']) {
            'supertype' => "\$this->$var = {$type}::cast(\$$var);",
            'metatype'  => "\$this->$var = \$$var instanceof {$type} ? \$$var : new {$type}(\$$var);",
            default     => "\$this->$var = \$$var;"
        };
    }

    public function phpType(PropertyNode $prop): string
    {
        $types = [];
        foreach ($prop->resolvedTypeInfo as $info) {
            if ($info['category'] === 'primitive') {
                // 'null' primitive is appended at the end via allowsNull() for consistent ordering
                if ($info['native'] !== 'null') {
                    $types[] = $info['native'];
                }
                continue;
            }

            if ($info['category'] === 'supertype') {
                $types[] = 'string';
                continue;
            }

            if (\in_array($info['category'], ['metatype', 'custom'], true)) {
                if (isset($prop->types)) {
                    $types = \array_merge($types, $prop->types);
                    continue;
                }
                $types[] = $prop->type;
            }
        }

        if ($this->allowsNull($prop)) {
            $types[] = 'null';
        }

        return \implode('|', \array_unique($types));
    }

    private function allowsNull(PropertyNode $prop): bool
    {
        foreach ($prop->resolvedTypeInfo as $info) {
            if ($info['category'] === 'primitive' && ($info['native'] ?? null) === 'null') {
                return true;
            }
        }
        return false;
    }
}
