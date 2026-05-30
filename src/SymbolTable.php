<?php

declare(strict_types=1);

namespace PHireScript;

use PHireScript\Compiler\External\ExternalClassDescriptor;
use PHireScript\Runtime\Exceptions\CompileException;

class SymbolTable
{
    private array $scopes = [[]];

    private array $functionReturns = [];
    private array $typeDefinitions = [];
    /** @var array<string, ExternalClassDescriptor> */
    private array $externals = [];

    private $functions = [];

    public function __construct()
    {
        $this->registerBuiltins();
    }

    public function registerTypeDefinition(string $name, $node)
    {
        $this->typeDefinitions[$name] = $node;
    }

    public function getTypeDefinition(string $name)
    {
        return $this->typeDefinitions[$name] ?? null;
    }

    public function enterScope()
    {
        \array_push($this->scopes, []);
    }

    public function getAllScopes()
    {
        return ($this->scopes);
    }

    public function exitScope()
    {
        \array_pop($this->scopes);
    }

    public function setType(string $name, mixed $type): void
    {
        $depth = \count($this->scopes) - 1;
        $this->scopes[$depth][$name] = $type;
    }

    public function getType(string $name): mixed
    {
        for ($i = \count($this->scopes) - 1; $i >= 0; $i--) {
            if (isset($this->scopes[$i][$name])) {
                return $this->scopes[$i][$name];
            }
        }
        return 'UNKNOWN';
    }

    public function registerBuiltins()
    {
        $this->functionReturns = [
        'toUpperCase' => 'STRING',
        'toLowerCase' => 'STRING',
        'join'        => 'STRING',
        'push'        => 'ARRAY',
        ];
    }

    public function registerFunction($name)
    {
        $this->functions[$name] = true;
    }

    public function isFunction($name)
    {
        return isset($this->functions[$name]);
    }

    public function registerExternal(string $alias, ExternalClassDescriptor $descriptor): void
    {
        if (isset($this->typeDefinitions[$alias])) {
            throw new CompileException(
                "External class '{$descriptor->className}' conflicts with a PHireScript"
                . " native class named '{$alias}'."
                . " Use 'external {$descriptor->className} as Alias'.",
                0,
                0,
            );
        }
        $this->externals[$alias] = $descriptor;
    }

    public function getExternal(string $alias): ?ExternalClassDescriptor
    {
        return $this->externals[$alias] ?? null;
    }

    public function isExternalClass(string $name): bool
    {
        return isset($this->externals[$name]);
    }
}
