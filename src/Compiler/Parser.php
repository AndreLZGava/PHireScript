<?php

namespace PHPScript\Compiler;

use PHPScript\Compiler\Parser\IdentifyTokenFactories\FactoryInitializer;
use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Runtime\RuntimeClass;

class Parser
{
    private array $factories;

    public function __construct(private array $config)
    {
        $this->factories = FactoryInitializer::getFactories();
    }

    public function parse($tokens, $path): Program
    {

        $program = new Program();
        $program->path = $path;
        $program->config = $this->config;
        $program->line = 0;
        $tokenManager = new TokenManager(RuntimeClass::CONTEXT_GENERAL, $tokens, 0);

        while (!$tokenManager->isEndOfTokens()) {
            $token = $tokenManager->getCurrentToken();
            $result = (new $this->factories[$token['type']]($tokenManager))
                ->process($program);

            if ($result) {
                $program->statements[] = $result;
            }

            $tokenManager->advance();
        }

        return $program;
    }
}
