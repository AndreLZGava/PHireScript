<?php

namespace PHPScript\Compiler;

use PHPScript\Compiler\Parser\IdentifyTokenFactories\FactoryInitializer;
use PHPScript\Compiler\Parser\Managers\TokenManager;

class Parser
{
    private array $factories;

    public function __construct()
    {
        $this->factories = FactoryInitializer::getFactories();
    }

    public function parse($tokens): Program
    {

        $program = new Program();
        $tokenManager = new TokenManager('general', $tokens, 0);

        while (!$tokenManager->isEndOfTokens()) {
            $token = $tokenManager->getCurrentToken();

            $result =  (new $this->factories[$token['type']]($tokenManager))
            ->process();

            if ($result) {
                $program->statements[] = $result;
            }

            $tokenManager->advance();
        }

        return $program;
    }
}
