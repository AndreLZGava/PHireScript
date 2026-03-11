<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbol;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Type;

class FactoryInitializer
{
    public static function getFactories(): array
    {
        return [
            'T_KEYWORD'     => Keywords::class,
            'T_IDENTIFIER'  => Method::class,
            'T_SYMBOL'      => Symbol::class,
            'T_TYPE'        => Type::class,
        ];
    }
}
