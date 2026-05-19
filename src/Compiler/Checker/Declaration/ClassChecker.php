<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Declaration;

use Exception;
use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

#[CompilerPass(order: 5)]
class ClassChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof ClassNode;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        $this->validateLifeCycleDefinition($node);
        $this->willCheck($node->body->children, $checker);
        return;
    }

    private function validateLifeCycleDefinition($node)
    {
        if (
            $node->type !== 'trait' &&
            !\in_array('abstract', $node->modifiers, true) &&
            \is_null($node->typeDependencyInjection)
        ) {
            throw new CompileException(
                $node->type . " " . $node->name .
                    " doesn't has a definition of life cycle. Please " .
                    "define it with \"as scoped\", \"as singleton\", \"as transient\"" .
                    " or \"newable\" after defining name of " . $node->type . "!",
                $node->token->line,
                $node->token->column
            );
        }
    }
}
