<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\Type;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\Variable;

class Keywords extends GlobalFactory
{
    private array $factories;

    public function process(): ?Node
    {
        $this->factories = [
        'type' => Type::class,
        'var' => Variable::class,
        ];

        $tokenValue = $this->tokenManager->getCurrentToken()['value'];
        $class = $this->factories[$tokenValue] ?? General::class;
        $processor = new $class($this->tokenManager);

        return $processor->process();
    }
}
