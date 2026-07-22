<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\ComplexObjectDefinition;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassExtendsNode;

class ExceptionNode extends ComplexObjectDefinition
{
    public ?ClassExtendsNode $extends = null;
    public ?string $messageTemplate = null;
    public array $properties = [];
    public bool $hasCustomConstructor = false;

    public function __construct(public Token $token)
    {
        $this->type = $token->value;
    }
}
