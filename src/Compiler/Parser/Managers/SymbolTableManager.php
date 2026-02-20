<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers;

use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class SymbolTableManager
{
    private array $typeDefinitions = [];
    private ?string $rawType = null;

    public function __construct()
    {
        $targetDir = __DIR__ . '/../../../../src/Runtime/DefaultOverrideMethods/Types';
        $getDefaultOverrideMethods = $this->scanAndBuildRegistry($targetDir);
        $this->typeDefinitions = $getDefaultOverrideMethods;
    }

    public function from($rawType): self
    {
        $this->rawType = $rawType;
        return $this;
    }

    public function getFunction($functionName): ?BaseMethods
    {
        if (is_null($this->rawType) || is_null($functionName)) {
            return null;
        }
        return $this->typeDefinitions[$this->rawType][$functionName] ?? null;
    }


    private function scanAndBuildRegistry(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new \RuntimeException("Dir not found: $directory");
        }

        $registry = [];
        $files = glob($directory . '/*.php');

        foreach ($files as $file) {
            $classesBefore = get_declared_classes();
            require_once $file;
            $classesAfter = get_declared_classes();
            $newClasses = array_diff($classesAfter, $classesBefore);

            foreach ($newClasses as $className) {
                $reflector = new \ReflectionClass($className);

                if (!$reflector->isInstantiable()) {
                    continue;
                }

                try {
                    $instance = $this->resolveAndInstantiate($reflector);
                } catch (\Throwable $e) {
                    error_log("It was not possible instantiate {$className}: " . $e->getMessage());
                    continue;
                }

                $shortName = $reflector->getShortName();
                $registry[$shortName] = [];

                $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);

                foreach ($methods as $method) {
                    $methodName = $method->getName();

                    if (str_starts_with($methodName, '__')) {
                        continue;
                    }

                    try {
                        $result = $method->invoke($instance);

                        $registry[$shortName][$methodName] = $result;
                    } catch (\Throwable $e) {
                        error_log("Error executing method {$className}::{$methodName}: " . $e->getMessage());
                    }
                }
            }
        }

        return $registry;
    }


    private function resolveAndInstantiate(\ReflectionClass $reflector): object
    {
        $constructor = $reflector->getConstructor();

        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            return $reflector->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $type = $param->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();
                    if ($typeName === 'array') {
                        $args[] = [];
                    } elseif ($typeName === 'int') {
                        $args[] = 0;
                    } elseif ($typeName === 'string') {
                        $args[] = '';
                    } elseif ($typeName === 'bool') {
                        $args[] = false;
                    } else {
                        $args[] = null;
                    }
                } else {
                    $args[] = null;
                }
            }
        }

        return $reflector->newInstanceArgs($args);
    }
}
