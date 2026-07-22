<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Declarations;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;

/**
 * Skips over a constructor { } block inside an exception declaration.
 * Counts brace depth and exits when the block closes.
 */
class ExceptionConstructorSkipContext extends AbstractContext
{
    private int $depth = 0;

    public function __construct()
    {
        parent::__construct(new class extends Node {
        });
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        if ($token->isOpeningCurlyBracket()) {
            $this->depth++;
            return null;
        }

        if ($token->isClosingCurlyBracket()) {
            if ($this->depth === 0) {
                $parseContext->contextManager->exit();
                return null;
            }
            $this->depth--;
        }

        return null;
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return false;
    }
}
