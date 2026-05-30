<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Binder\Declaration;

use PHireScript\Compiler\Binder as CompilerBinder;
use PHireScript\Compiler\Binder\Binder;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\External\ExternalClassDescriptor;
use PHireScript\Compiler\External\ExternalConstantInfo;
use PHireScript\Compiler\External\ExternalConstructorInfo;
use PHireScript\Compiler\External\ExternalMemberInfo;
use PHireScript\Compiler\External\ExternalParamInfo;
use PHireScript\Compiler\External\ExternalPropertyInfo;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ExternalNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Runtime\Exceptions\CompileException;

#[CompilerPass(order: 2)]
class ExternalBinder implements Binder
{
    public function mustBind(Node $node): bool
    {
        return $node instanceof ExternalNode;
    }

    public function bind(Node $node, CompilerBinder $binder): void
    {
        assert($node instanceof ExternalNode);
        foreach ($node->namespaces as $namespace) {
            $fqcn  = $namespace->namespace;
            $alias = $namespace->alias ?? $fqcn;

            if (!class_exists($fqcn, true) && !interface_exists($fqcn, true)) {
                \PHireScript\Helper\Messenger::warning(
                    "Cannot load external class '{$fqcn}': not found in autoloader. " .
                    "Calls to members of this class will not be validated."
                );
                // Register empty descriptor so the alias is known but unvalidatable
                $emptyDescriptor = new ExternalClassDescriptor(
                    className:   $fqcn,
                    alias:       $alias,
                    methods:     [],
                    constants:   [],
                    constructor: null,
                    properties:  [],
                );
                $binder->globalTable->registerExternal($alias, $emptyDescriptor);
                continue;
            }

            $descriptor = $this->buildDescriptor($fqcn, $alias);
            $binder->globalTable->registerExternal($alias, $descriptor);
        }
    }

    private function buildDescriptor(string $fqcn, string $alias): ExternalClassDescriptor
    {
        /** @var class-string $classString */
        $classString = $fqcn;
        $ref = new \ReflectionClass($classString);

        return new ExternalClassDescriptor(
            className:   $fqcn,
            alias:       $alias,
            methods:     $this->buildMethods($ref),
            constants:   $this->buildConstants($ref),
            constructor: $this->buildConstructor($ref),
            properties:  $this->buildProperties($ref),
        );
    }

    /** @return array<string, ExternalMemberInfo> */
    private function buildMethods(\ReflectionClass $ref): array
    {
        $methods = [];
        foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor() || $method->isDestructor()) {
                continue;
            }
            $methods[$method->getName()] = new ExternalMemberInfo(
                name:               $method->getName(),
                isStatic:           $method->isStatic(),
                returnType:         $this->resolveReturnType($method),
                requiredParamCount: $method->getNumberOfRequiredParameters(),
            );
        }
        return $methods;
    }

    /** @return string|string[]|null */
    private function resolveReturnType(\ReflectionMethod $method): string|array|null
    {
        $type = $method->getReturnType();
        if ($type === null) {
            return null;
        }
        if ($type instanceof \ReflectionUnionType) {
            /** @var string[] $names */
            $names = array_map(
                static fn (\ReflectionType $t): string => $t instanceof \ReflectionNamedType
                    ? $t->getName()
                    : (string) $t,
                $type->getTypes()
            );
            return $names;
        }
        if ($type instanceof \ReflectionNamedType) {
            $name = $type->getName();
            return $name === 'mixed' ? null : $name;
        }
        return null;
    }

    /** @return array<string, ExternalConstantInfo> */
    private function buildConstants(\ReflectionClass $ref): array
    {
        $constants = [];
        foreach ($ref->getReflectionConstants(\ReflectionClassConstant::IS_PUBLIC) as $const) {
            $constants[$const->getName()] = new ExternalConstantInfo(
                name:  $const->getName(),
                value: $const->getValue(),
            );
        }
        return $constants;
    }

    private function buildConstructor(\ReflectionClass $ref): ?ExternalConstructorInfo
    {
        $ctor = $ref->getConstructor();
        if ($ctor === null) {
            return new ExternalConstructorInfo(isPublic: true, requiredParams: [], optionalParams: []);
        }

        $required = [];
        $optional = [];
        foreach ($ctor->getParameters() as $param) {
            $info = new ExternalParamInfo(
                name:       $param->getName(),
                type:       $param->hasType() ? (string) $param->getType() : null,
                hasDefault: $param->isOptional(),
            );
            if ($param->isOptional()) {
                $optional[] = $info;
            } else {
                $required[] = $info;
            }
        }

        return new ExternalConstructorInfo(
            isPublic:       $ctor->isPublic(),
            requiredParams: $required,
            optionalParams: $optional,
        );
    }

    /** @return array<string, ExternalPropertyInfo> */
    private function buildProperties(\ReflectionClass $ref): array
    {
        $properties = [];
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $type = $prop->hasType() ? (string) $prop->getType() : null;
            $properties[$prop->getName()] = new ExternalPropertyInfo(
                name: $prop->getName(),
                type: $type,
            );
        }
        return $properties;
    }
}
