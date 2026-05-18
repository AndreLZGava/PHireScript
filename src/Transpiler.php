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
use PHireScript\Compiler\Scanner;
use PHireScript\Compiler\Validator;
use PHireScript\Core\CompilerContext;
use PHireScript\Helper\Debug\Debug;

class Transpiler implements TranspilerInterface
{
    private readonly PreprocessorInterface $generator;
    private string $codeBeforeGenerator;

    public function __construct(
        private readonly array $config,
        private readonly DependencyGraphBuilder $dependencyManager,
        private readonly CompilerContext $context,
        private readonly ?CacheManager $cache = null,
    ) {
        $this->generator = new PhpFileGeneratorHandler(false);
    }

    public function compile(string $code, string $path): string
    {
        $this->codeBeforeGenerator = '';

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

        $symbolTable = new SymbolTable();
        $binder = new Binder($symbolTable);
        $updatedAst = $binder->bind($ast);

        $checker = new Checker($symbolTable);
        $checker->check($updatedAst);

        $emitter = new Emitter($this->config, $this->dependencyManager);
        $preCompiledPhpCode = $emitter->emit($updatedAst);

        $this->codeBeforeGenerator = $preCompiledPhpCode;
        $result = $this->generator->process($preCompiledPhpCode);
        return $result;
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
