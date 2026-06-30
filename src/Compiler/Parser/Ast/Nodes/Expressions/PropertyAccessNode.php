<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Expressions;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression\Types\Type;

class PropertyAccessNode extends Expression implements Type
{
    public Type $type;
    public ?string $resolvedType = null;

    public function __construct(
        Token $token,
        public Node $object,
        public Node|string $property
    ) {
        $this->type = $this;
    }

    public function getRawType(): string
    {
        return $this->resolvedType ?? 'PropertyAccess';
    }
}
