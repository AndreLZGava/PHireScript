<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Binder\Declaration;

use PHireScript\Compiler\Binder as CompilerBinder;
use PHireScript\Compiler\Binder\Binder;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\InterfaceNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;

class ClassBodyBinder implements Binder
{
    public function mustBind(Node $node): bool
    {
        return $node instanceof ClassNode || $node instanceof InterfaceNode;
    }

    public function bind(Node $node, CompilerBinder $binder): void
    {
        if (isset($node->with)) {
            array_unshift($node->body->children, $node->with);
        }
    }
}
