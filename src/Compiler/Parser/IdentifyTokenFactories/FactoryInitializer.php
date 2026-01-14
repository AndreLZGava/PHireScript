<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\IdentifyTokenFactories\Comment;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\EndOfLine;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Symbol;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Type;

class FactoryInitializer
{
    public static function getFactories(): array
    {
        return [
            'T_COMMENT'     => Comment::class,
            //'T_STRING_LIT'  => Comment::class,
            'T_NUMBER'      => Number::class,
            'T_KEYWORD'     => Keywords::class,
            //'T_BOOL'        => Comment::class,
            'T_EOL'         => EndOfLine::class,
            //'T_WHITESPACE'  => Comment::class,
            //'T_MODIFIER'    => Comment::class,
            //'T_VARIABLE'    => Comment::class,
            'T_IDENTIFIER'  => Method::class,
            'T_SYMBOL'      => Symbol::class,
            'T_TYPE'        => Type::class,
        ];
    }
}
