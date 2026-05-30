<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser;

use Exception;
use PHireScript\Compiler\Parser\Managers\ContextManager;
use PHireScript\Compiler\Parser\Managers\SymbolTableManager;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\Managers\VariableManager;
use PHireScript\Compiler\Program;
use PHireScript\Core\CompilerContext;
use PHireScript\DependencyGraphBuilder;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;
use PHireScript\Runtime\RuntimeClass;

class ParseContext
{
    /** @var array<string, string> alias → FQCN for external class declarations */
    public array $externalAliases = [];

    /** @var array<string, string> varName → external alias for variables inferred as external type */
    public array $externalVarTypes = [];

    public function __construct(
        public VariableManager $variables,
        public Program $program,
        // public ParserDispatcher $emitter,
        public TokenManager $tokenManager,
        public SymbolTableManager $symbolTable,
        public CompilerContext $compilerContext,
        public ?DependencyGraphBuilder $dependencyBuilder = null,
        public ?ContextManager $contextManager = null,
        private mixed $previous = null,
        private ?string $currentPackage = null,
    ) {
    }

    public function registerExternalAlias(string $alias, string $fqcn): void
    {
        $this->externalAliases[$alias] = $fqcn;
    }

    public function isExternalAlias(string $name): bool
    {
        return isset($this->externalAliases[$name]);
    }

    public function registerExternalVarType(string $varName, string $externalAlias): void
    {
        $this->externalVarTypes[$varName] = $externalAlias;
    }

    public function isExternalVarType(string $varName): bool
    {
        return isset($this->externalVarTypes[$varName]);
    }

    public function setCurrentPackage(string $package)
    {
        $this->currentPackage = $package;
    }

    public function getCurrentPackage(): ?string
    {
        return $this->currentPackage ?? null;
    }

    public function definePrevious(mixed $previous): void
    {
        if (!empty($this->previous) && $previous !== $this->previous) {
            Debug::trace($this->previous, $previous);
            throw new CompileException(
                'Previous already defined, please consume it before new assignment!',
                $previous->line,
                $previous->column
            );
        }
        $this->previous = $previous;
    }

    public function consumePrevious(): mixed
    {
        $previous = $this->previous;
        $this->previous = null;
        return $previous;
    }

    public function peekPrevious(): mixed
    {
        return  $this->previous;
    }
}
