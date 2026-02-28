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
class ReturnTypeContext extends AbstractContext
{
    protected ?Node $returnType = null;

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        return null;
    }

    public function getReturnType(): ?Node
    {
        return $this->returnType;
    }
}
