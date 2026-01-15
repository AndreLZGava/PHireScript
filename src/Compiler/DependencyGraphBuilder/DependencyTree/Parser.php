<?php

namespace PHPScript\Compiler\DependencyGraphBuilder\DependencyTree;

use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Compiler\Program;
use PHPScript\Runtime\RuntimeClass;

class Parser
{
    public function __construct(private array $config)
    {
    }

    public function parse($tokens, $path): Program
    {
        $program = new Program();
        $program->config = $this->config;
        $program->path = $path;
        $program->line = 0;
        $tokenManager = new TokenManager(RuntimeClass::CONTEXT_GENERAL, $tokens, 0);

        while (!$tokenManager->isEndOfTokens()) {
            $result = FactoryDependencies::getFactories(
                $tokenManager,
                $program
            );

            if ($result) {
                  $program->statements[] = $result;
            }

            $tokenManager->advance();
        }

        return $program;
    }
}
