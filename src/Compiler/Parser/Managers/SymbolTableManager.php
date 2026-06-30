<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers;

use PHireScript\Cache\CacheManager;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableReferenceNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class SymbolTableManager
{
    private const TYPES_DIR = __DIR__ . '/../../../../src/Runtime/DefaultOverrideMethods/Types';

    /** @var array<string, array<string, BaseMethods>>|null In-memory registry shared across all instances in one process. */
    private static ?array $staticRegistry = null;

    private array $typeDefinitions = [];
    private ?string $rawType = null;
    private array $lastExecution = [];

    public function __construct(?CacheManager $cache = null)
    {
        $targetDir = realpath(self::TYPES_DIR) ?: self::TYPES_DIR;
        $this->typeDefinitions = $this->loadRegistry($targetDir, $cache);
    }

    public function from($rawType): self
    {
        $this->rawType = $rawType;
        return $this;
    }

    public function getFunctionFromLastExecution(
        string $functionName,
        bool $mustUpdate = false,
    ): ?BaseMethods {
        if (empty($this->lastExecution)) {
            return null;
        }
        $allowedParams = \count($this->lastExecution) === 1 ? \current($this->lastExecution) : (
            \count($this->lastExecution) === 0 ? [] : \implode('|', $this->lastExecution)
        );

        if (
            !\array_key_exists($allowedParams . 'Methods', $this->typeDefinitions) ||
            !\array_key_exists($functionName, $this->typeDefinitions[$allowedParams . 'Methods'])
        ) {
            return null;
        }

        $function = $this->typeDefinitions[$allowedParams . 'Methods'][$functionName];
        if ($function && $mustUpdate) {
            $this->lastExecution = $function->returnOfPhpExecution;
        }
        return $function ?? null;
    }

    public function getFunction($functionName): ?BaseMethods
    {
        if (is_null($this->rawType) || is_null($functionName)) {
            return null;
        }

        $typeKey = $this->rawType . 'Methods';
        if (\array_key_exists($typeKey, $this->typeDefinitions)) {
            $function = $this->typeDefinitions[$typeKey][$functionName] ?? null;
            if ($function) {
                $this->lastExecution = $function->returnOfPhpExecution;
                return $function;
            }
        }

        // Fallback to GeneralType for methods available on all types (defined?, is?, show!, etc.)
        $general = $this->typeDefinitions['GeneralType'][$functionName] ?? null;
        if ($general) {
            $this->lastExecution = $general->returnOfPhpExecution;
            return $general;
        }

        return null;
    }

    /**
     * Load the type-method registry, using the CacheManager when available.
     *
     * @return array<string, array<string, BaseMethods>>
     */
    private function loadRegistry(string $directory, ?CacheManager $cache): array
    {
        // Ultra-fast path: in-memory static (same PHP process).
        if (self::$staticRegistry !== null) {
            return self::$staticRegistry;
        }

        // Fast path: disk cache hit.
        if ($cache !== null && $cache->areTypeSourcesValid($directory)) {
            /** @var array<string, array<string, BaseMethods>>|null $cached */
            $cached = $cache->getTypeMethods();

            if ($cached !== null) {
                self::$staticRegistry = $cached;
                return self::$staticRegistry;
            }
        }

        // Slow path: reflection-based discovery.
        $registry = $this->scanAndBuildRegistry($directory);

        if ($cache !== null) {
            $cache->setTypeMethods($registry);
            $cache->touchTypesSources($directory);
        }

        self::$staticRegistry = $registry;
        return self::$staticRegistry;
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

                        $registry[$shortName][$result->name] = $result;
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
