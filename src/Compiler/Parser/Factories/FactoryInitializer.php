<?php

namespace PHPScript\Compiler\Parser\Factories;

use Closure;
use PHPScript\Compiler\Parser\Node;
use PHPScript\Compiler\Parser\Program;
use PHPScript\Compiler\Parser\ClassDefinition;
use PHPScript\Compiler\Parser\Factories\EndOfLine;
use PHPScript\Compiler\Parser\Factories\Comment;
use PHPScript\Compiler\Parser\Factories\Keywords;
use PHPScript\Compiler\Parser\MethodDefinition;
use PHPScript\Compiler\Parser\PropertyDefinition;
use PHPScript\Compiler\Parser\GlobalStatement;

class FactoryInitializer {
  public static function getFactories(): array {
    return [
      'T_COMMENT'     => Comment::class,
      'T_STRING_LIT'  => Comment::class,
      'T_NUMBER'      => Comment::class,
      'T_KEYWORD'     => Keywords::class,
      'T_BOOL'        => Comment::class,
      'T_EOL'         => EndOfLine::class,
      'T_WHITESPACE'  => Comment::class,
      'T_MODIFIER'    => Comment::class,
      'T_VARIABLE'    => Comment::class,
      'T_IDENTIFIER'  => Comment::class,
      'T_SYMBOL'      => Symbol::class,
      'T_TYPE'        => Type::class,
    ];
  }
}
