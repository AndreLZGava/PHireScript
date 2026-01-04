<?php

namespace PHPScript\Compiler\Scanner\Factories;

use Closure;
use PHPScript\Compiler\Scanner\Node;
use PHPScript\Compiler\Scanner\Program;
use PHPScript\Compiler\Scanner\ClassDefinition;
use PHPScript\Compiler\Scanner\Factories\EndOfLine;
use PHPScript\Compiler\Scanner\Factories\Comment;
use PHPScript\Compiler\Scanner\Factories\Keywords;
use PHPScript\Compiler\Scanner\MethodDefinition;
use PHPScript\Compiler\Scanner\PropertyDefinition;
use PHPScript\Compiler\Scanner\GlobalStatement;

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
