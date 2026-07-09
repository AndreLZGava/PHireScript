<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Ast\Nodes\Expression\Types\Type;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class NamedArgNode extends Node implements Type
{
    public function __construct(
        Token $token,
        public string $paramName,
        public ?Node $value = null,
    ) {
        parent::__construct($token);
    }

    public function getRawType(): string
    {
        if ($this->value instanceof Type) {
            return $this->value->getRawType();
        }
        return 'Mixed';
    }
}
