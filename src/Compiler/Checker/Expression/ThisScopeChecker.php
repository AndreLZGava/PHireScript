<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Expression;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ThisExpressionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Program;
use PHireScript\Runtime\Exceptions\CheckerException;

#[CompilerPass(order: 9)]
class ThisScopeChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof Program;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        assert($node instanceof Program);

        foreach ($node->statements as $statement) {
            if ($statement instanceof ClassNode) {
                continue;
            }

            $this->assertNoThis($statement);
        }
    }

    private function assertNoThis(mixed $node, int $depth = 0): void
    {
        if ($depth > 20 || !is_object($node)) {
            return;
        }

        if ($node instanceof ThisExpressionNode) {
            throw new CheckerException(
                "'this' is not valid outside a class, type, or immutable context.",
                $node->token->line,
                $node->token->column
            );
        }

        foreach ((array) $node as $value) {
            if (is_object($value)) {
                $this->assertNoThis($value, $depth + 1);
            } elseif (is_array($value)) {
                foreach ($value as $item) {
                    if (is_object($item)) {
                        $this->assertNoThis($item, $depth + 1);
                    }
                }
            }
        }
    }
}
