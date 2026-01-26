<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\CommentStatement;
use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

class Comment extends GlobalFactory
{
    public function process(Program $program, ParseContext $parseContext): ?Node
    {
        $node = new CommentStatement($this->tokenManager->getCurrentToken());
        $node->code = trim((string) $this->tokenManager->getCurrentToken()->value);
        return $node;
    }
}
