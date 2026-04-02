<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Declaration;

use Exception;
use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\Parser\Ast\Nodes\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

class ClassChecker implements Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof ClassNode;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        $this->validateDependencyInjection($node);
        return;
    }

    private function validateDependencyInjection($node)
    {
        if (
            $node->type !== 'trait' &&
            !\in_array('abstract', $node->modifiers) &&
            \is_null($node->typeDependencyInjection)
        ) {
            throw new CompileException(
                $node->token->value . " " . $node->name .
                    " doesn't has a definition of dependency injection. Please " .
                    "define it with \"as scoped\" or \"as singleton\" after defining name!",
                $node->token->line,
                $node->token->column
            );
        }
    }
}
