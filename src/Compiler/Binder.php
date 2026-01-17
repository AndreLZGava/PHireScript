<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\SymbolTable;
use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Helper\Debug\Debug;

class Binder
{
    public function __construct(private readonly SymbolTable $globalTable)
    {
    }

    public function bind(Program $program)
    {
        // PASSAGEM 1: Registrar a existência de todas as classes
        // Isso permite que uma classe use outra como tipo, mesmo se definida depois
        foreach ($program->statements as $node) {
            if ($node instanceof ClassDefinition) {
                $this->globalTable->registerTypeDefinition($node->name, $node);
            }
        }

        // PASSAGEM 2: Resolver as propriedades e corpos
        foreach ($program->statements as $node) {
            if ($node instanceof ClassDefinition) {
                $this->bindClassBody($node);
            }
        }

        return $program;
    }

    protected function bindClassBody(ClassDefinition $class)
    {
        foreach ($class->body as $member) {
            if ($member instanceof PropertyDefinition) {
                $this->resolvePropertyTypes($member);
            }

            if ($member instanceof MethodDefinition) {
                $this->resolvePropertyTypeForMethods($member);
            }
        }
    }

    protected function resolvePropertyTypeForMethods(MethodDefinition $prop)
    {
        foreach ($prop->args as $propertyDefinition) {
            $this->resolvePropertyTypes($propertyDefinition);
        }
    }

    protected function resolvePropertyTypes(PropertyDefinition $prop)
    {
        $typeString = $prop->type;
        $types = str_contains((string) $typeString, '|') ? explode('|', (string) $typeString) : [$typeString];

        $resolved = [];
        foreach ($types as $type) {
            $resolved[] = $this->categorizeType($type);
        }

        $prop->resolvedTypeInfo = $resolved;
    }

    protected function categorizeType(string $typeName): array
    {
        $primitives = [
            'String' => 'string',
            'Int'    => 'int',
            'Float'  => 'float',
            'Bool'   => 'bool',
            'Object' => 'object',
            'Array'  => 'array'
        ];

        if (isset($primitives[$typeName])) {
            return ['category' => 'primitive', 'native' => $primitives[$typeName]];
        }

        $metaTypes = ['Date', 'Currency', 'Phone'];
        if (in_array($typeName, $metaTypes, true)) {
            return ['category' => 'metatype', 'class' => "PHireScript\\Runtime\\Types\\MetaTypes\\$typeName"];
        }

        $superTypes = ['Email', 'Ipv4', 'Ipv6', 'Url'];
        if (in_array($typeName, $superTypes, true)) {
            return ['category' => 'supertype', 'class' => "PHireScript\\Runtime\\Types\\SuperTypes\\$typeName"];
        }

        // Se não for nada acima, verificamos se é uma classe que já registramos na Passagem 1
        $isRegistered = $this->globalTable->getTypeDefinition($typeName);

        return [
            'category' => $isRegistered ? 'custom' : 'unknown',
            'name' => $typeName
        ];
    }
}
