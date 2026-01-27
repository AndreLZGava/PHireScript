<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Parser\IdentifyTokenFactories\FactoryInitializer;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\Managers\VariableManager;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

class Parser
{
    private array $factories;

    public function __construct(private readonly array $config)
    {
        $this->factories = FactoryInitializer::getFactories();
    }

    public function parse($tokens, $path): Program
    {
        $tokenManager = new TokenManager(RuntimeClass::CONTEXT_GENERAL, $tokens, 0);
        $program = new Program($tokenManager->getCurrentToken());
        $variableManager = new VariableManager();
        $parseContext = new ParseContext(variables: $variableManager);

        $program->path = $path;
        $program->config = $this->config;
        while (!$tokenManager->isEndOfTokens()) {
            $token = $tokenManager->getCurrentToken();
            $result = (new $this->factories[$token->type]($tokenManager))
            ->process($program, $parseContext);
            if ($result) {
                $program->statements[] = $result;
            }

            $tokenManager->advance();
        }

        return $program;
    }
}
