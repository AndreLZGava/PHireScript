<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Binder\Declaration\Class;

use PHireScript\Compiler\Binder as CompilerBinder;
use PHireScript\Compiler\Binder\Binder;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;

#[CompilerPass(order: 8)]
class MethodParamResolutionBinder implements Binder
{
    public function mustBind(Node $node): bool
    {
        return $node instanceof MethodDeclarationNode;
    }

    public function bind(Node $node, CompilerBinder $binder): void
    {
        foreach ($binder->binders as $check) {
            foreach ($node->parameters?->params ?? [] as $param) {
                if ($check->mustBind($param)) {
                    $check->bind($param, $binder);
                }
            }
        }
    }
}
