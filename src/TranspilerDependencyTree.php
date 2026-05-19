<?php

declare(strict_types=1);

namespace PHireScript;

use PHireScript\Cache\CacheManager;
use PHireScript\Compiler\DependencyGraphBuilder\DependencyTree\Parser;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Scanner;
use PHireScript\Core\CompilerContext;

class TranspilerDependencyTree
{
    private string $codeBeforeGenerator = '';

    public function __construct(
        private readonly array $config,
        private readonly CompilerContext $context,
        private readonly ?CacheManager $cache = null,
    ) {
    }

    public function compile(string $code, string $path): Program
    {
        $tokens = $this->resolveTokens($code, $path);

        $parser = new Parser(
            $this->config,
            new DependencyGraphBuilder(),
            $this->context,
            $this->cache,
        );
        $ast = $parser->parse($tokens, $path);

        return $ast;
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
