<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Binder\Root;

use PHireScript\Compiler\Binder as CompilerBinder;
use PHireScript\Compiler\Binder\Binder;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\InterfaceNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Program;

#[CompilerPass(order: 1)]
class TypeRegistrationBinder implements Binder
{
    public function mustBind(Node $node): bool
    {
        return $node instanceof Program;
    }

    public function bind(Node $node, CompilerBinder $binder): void
    {
        foreach ($node->statements as $statement) {
            if ($statement instanceof ClassNode || $statement instanceof InterfaceNode) {
                $binder->globalTable->registerTypeDefinition($statement->name, $statement);
            }
        }
    }
}
