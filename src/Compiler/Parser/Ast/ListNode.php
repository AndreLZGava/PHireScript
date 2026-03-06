<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use PHireScript\Compiler\Parser\Ast\Expression\Types\Type;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class ListNode extends Collection implements Type {
    private string $raw = 'List';
    public readonly array $keys;
    public function __construct(
        public Token $token,
        public array $types = [],
    ) {
        $this->keys = ['Int'];
    }

    public function getRawType(): string {
        return $this->raw;
    }
}
