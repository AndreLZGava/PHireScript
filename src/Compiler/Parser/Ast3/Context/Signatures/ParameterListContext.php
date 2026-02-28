<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Signatures;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;

/**
 * @extends AbstractContext<ParamsNode>
 */
class ParameterListContext extends AbstractContext
{
    protected array $parameters = [];

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        return null;
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->value === ')';
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
