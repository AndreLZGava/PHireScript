<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ClassKey;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\InterfaceKey;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ReturnKey;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\Type;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\Variable;
use PHPScript\Helper\Debug\Debug;

class Keywords extends GlobalFactory
{
    private array $factories;

    public function process(): ?Node
    {
        $this->factories = [
            'type' => Type::class,
            'var' => Variable::class,
            'interface' => InterfaceKey::class,
            'class' => ClassKey::class,
            'return' => ReturnKey::class,
        ];

        $tokenValue = $this->tokenManager->getCurrentToken()['value'];

        if (!isset($this->factories[$tokenValue])) {
            Debug::show($tokenValue);
        }

        $class = $this->factories[$tokenValue] ?? General::class;
        $processor = new $class($this->tokenManager);

        return $processor->process();
    }
}
