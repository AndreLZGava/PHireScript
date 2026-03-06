<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Ast\Expression\Types\Type;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class ListNode extends Collection implements Type
{
     private string $raw = 'List';
    public function __construct(
        public Token $token,
        public array $types = [],
    ) {
    }

    public function getRawType(): string
    {
        return $this->raw;
    }
}
