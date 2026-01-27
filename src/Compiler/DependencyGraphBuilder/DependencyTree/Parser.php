<?php

namespace PHireScript\Compiler\DependencyGraphBuilder\DependencyTree;

use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\Managers\VariableManager;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Runtime\RuntimeClass;

class Parser
{
    public function __construct(private array $config)
    {
    }

    public function parse($tokens, $path): Program
    {
        $tokenManager = new TokenManager(RuntimeClass::CONTEXT_GENERAL, $tokens, 0);
        $program = new Program($tokenManager->getCurrentToken());
        $program->config = $this->config;
        $program->path = $path;
        $program->line = 0;
        $parseContext = new ParseContext(variables: new VariableManager());
        while (!$tokenManager->isEndOfTokens()) {
            $result = FactoryDependencies::getFactories(
                $tokenManager,
                $program,
                $parseContext
            );

            if ($result) {
                $program->statements[] = $result;
            }

            $tokenManager->advance();
        }

        return $program;
    }
}
