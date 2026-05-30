<?php

declare(strict_types=1);

namespace PHireScript;

use PHireScript\Cache\CacheManager;
use PHireScript\Compiler\Binder;
use PHireScript\Compiler\Checker;
use PHireScript\Compiler\Emitter;
use PHireScript\Compiler\Parser;
use PHireScript\Compiler\Processors\PhpFileGeneratorHandler;
use PHireScript\Compiler\Processors\PreprocessorInterface;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Scanner;
use PHireScript\Compiler\Validator;
use PHireScript\Core\CompilerContext;

class Transpiler implements TranspilerInterface
{
    private readonly PreprocessorInterface $generator;
    private string $codeBeforeGenerator;

    /** @var array<string, Program> */
    private array $boundAsts = [];

    public function __construct(
        private readonly array $config,
        private readonly DependencyGraphBuilder $dependencyManager,
        private readonly CompilerContext $context,
        private readonly ?CacheManager $cache = null,
        private readonly ?SymbolTable $symbolTable = new SymbolTable(),
    ) {
        $this->generator  = new PhpFileGeneratorHandler();
    }

    /**
     * Phase 0 — scan, validate, and parse one file; no binding.
     * Returns an unbound AST suitable for dependency-graph construction.
     * The result is persisted to the AST cache so unchanged files are not
     * re-parsed on the next build.
     */
    public function parseOnly(string $code, string $path): Program
    {
        if ($this->cache !== null) {
            $cached = $this->cache->getProgram($path);
            if ($cached !== null) {
                return $cached;
            }
        }

        $tokens = $this->resolveTokens($code, $path);

        $validator = new Validator();
        /** @var array<\PHireScript\Compiler\Parser\Managers\Token\Token> $typedTokens */
        $typedTokens = $tokens;
        $validator->validate($typedTokens);

        $parser = new Parser(
            $this->config,
            $this->dependencyManager,
            $this->context,
            $this->cache,
        );
        $ast = $parser->parse($tokens, $path);

        if ($this->cache !== null) {
            $this->cache->setProgram($path, $ast);
        }

        return $ast;
    }

    /**
     * Phase 1a — bind an already-parsed AST, storing it for Phase 2.
     * Populates the shared SymbolTable with type definitions from this file.
     */
    public function bindProgram(Program $ast, string $path): Program
    {
        $binder   = new Binder($this->symbolTable ?? new SymbolTable());
        $boundAst = $binder->bind($ast);
        $this->boundAsts[$path] = $boundAst;

        return $boundAst;
    }

    /**
     * Phase 1 — parse and bind one file, storing the bound AST for Phase 2.
     * Populates the shared SymbolTable with type definitions from this file.
     */
    public function parseAndBind(string $code, string $path): Program
    {
        return $this->bindProgram($this->parseOnly($code, $path), $path);
    }

    /**
     * Phase 2 — type-check and emit one already-bound AST.
     * Uses the shared SymbolTable (fully populated by Phase 1).
     */
    public function checkAndEmit(Program $ast): string
    {
        $checker = new Checker($this->symbolTable ?? new SymbolTable());
        $checker->check($ast);

        $emitter = new Emitter($this->config, $this->dependencyManager, $this->symbolTable);
        $preCompiledPhpCode = $emitter->emit($ast);

        $this->codeBeforeGenerator = $preCompiledPhpCode;

        return $this->generator->process($preCompiledPhpCode);
    }

    /**
     * Full pipeline for a single file. Uses a Phase-1 bound AST when available
     * (populated by Compiler before loadAndCompile), otherwise falls back to a
     * fresh parse+bind inline.
     */
    public function compile(string $code, string $path): string
    {
        $this->codeBeforeGenerator = '';

        if (isset($this->boundAsts[$path])) {
            $ast = $this->boundAsts[$path];
            unset($this->boundAsts[$path]);
        } else {
            $ast = $this->parseAndBind($code, $path);
            unset($this->boundAsts[$path]);
        }

        return $this->checkAndEmit($ast);
    }

    public function getCodeBeforeGenerator(): string
    {
        return $this->codeBeforeGenerator ?? '';
    }

    /**
     * Resolve tokens from cache or by scanning the source file.
     *
     * @return array<int, mixed>
     */
    private function resolveTokens(string $code, string $path): array
    {
        if ($this->cache !== null) {
            $cached = $this->cache->getTokens($path);

            if ($cached !== null) {
                return $cached;
            }
        }

        $scanner = new Scanner($code, $path);
        $tokens = $scanner->tokenize();

        if ($this->cache !== null) {
            $this->cache->setTokens($path, $tokens);
        }

        return $tokens;
    }
}
