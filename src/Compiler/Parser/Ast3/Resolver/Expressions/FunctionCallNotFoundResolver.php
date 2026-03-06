<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

class FunctionCallNotFoundResolver implements ContextTokenResolver {
  public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool {
    return $token->isIdentifier() &&
      $parseContext->tokenManager->getNextTokenAfterCurrent()->value === '(';
  }

  public function resolve(
    Token $token,
    ParseContext $parseContext,
    AbstractContext $context
  ): void {
    throw new CompileException("This method is not supported for this type of variable", $token->line, $token->column);
  }
}
