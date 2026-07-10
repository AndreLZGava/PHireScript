<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Cache\CacheManager;
use PHireScript\Compiler\Parser\Ast\Context\Root\ProgramContext;
use PHireScript\Compiler\Parser\Managers\ContextManager;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\Managers\VariableManager;
use PHireScript\Compiler\Parser\Managers\SymbolTableManager;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Core\CompilerContext;
use PHireScript\DependencyGraphBuilder;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

class Parser
{
    /** Shared across all parse() calls in a single build — enables cross-file inheritance lookup. */
    private array $sharedClassRegistry = [];

    public function __construct(
        private readonly array $config,
        protected DependencyGraphBuilder $dependencyBuilder,
        private readonly CompilerContext $context,
        private readonly ?CacheManager $cache = null,
    ) {
    }

    public function parse(array $tokens, string $path): Program
    {
        //Debug::show($tokens);exit;
        $tokenManager = new TokenManager(
            RuntimeClass::CONTEXT_GENERAL,
            $tokens,
            0
        );

        $program = new Program($tokenManager->getCurrentToken());

        $parseContext = new ParseContext(
            variables: new VariableManager(),
            program: $program,
            tokenManager: $tokenManager,
            contextManager: null,
            symbolTable: new SymbolTableManager($this->cache),
            dependencyBuilder: $this->dependencyBuilder,
            compilerContext: $this->context,
        );
        $parseContext->classMethodRegistry = &$this->sharedClassRegistry;

        $rootContext = new ProgramContext($parseContext);

        $contextManager = new ContextManager($rootContext);

        $parseContext->contextManager = $contextManager;

        $contextManager->setPath($path);
        $contextManager->setConfig($this->config);

        while (!$tokenManager->isEndOfTokens()) {
            $token = $tokenManager->getCurrentToken();

            $contextManager->handle($token, $parseContext);

            $tokenManager->advance();
        }
        //Debug::show($program->statements);exit;
        return $program;
    }
}
