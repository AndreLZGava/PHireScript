<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\IdentifyTokenFactories\Comment;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\EndOfLine;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbol;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Type;

class FactoryInitializer
{
    public static function getFactories(): array
    {
        return [
            'T_COMMENT'     => Comment::class,
            'T_STRING_LIT'  => StringLiteral::class,
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
