<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Expression;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Runtime\Exceptions\CheckerException;

#[CompilerPass(order: 50)]
class ChainConsistencyChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof FunctionNode && $node->isChainLink;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        /** @var FunctionNode $node */
        $vb = $node->variableBase;

        if (!($vb instanceof FunctionNode)) {
            return;
        }

        $returnTypes = $vb->method->returnOfPhpExecution;

        // Rule 2: Void terminates chain
        if (empty($returnTypes) || $returnTypes === ['Void']) {
            throw new CheckerException(
                'Cannot chain after void method `' . $vb->method->name . '`',
                $node->token->line,
                $node->token->column
            );
        }

        // Rule 3: Nullable requires ?.
        if (\in_array('Null', $returnTypes, true) && !$vb->safeNavigation) {
            $msg = 'Method `' . $vb->method->name . '` may return `Null`.'
                . ' Use `?.` to propagate or assign and check before chaining.';
            throw new CheckerException($msg, $node->token->line, $node->token->column);
        }

        // Rule 5: Mixed blocks direct chain
        if ($returnTypes === ['Mixed']) {
            $msg = 'Cannot chain directly after `Mixed` return from `' . $vb->method->name . '`.'
                . ' Assign to a variable and verify type before chaining.';
            throw new CheckerException($msg, $node->token->line, $node->token->column);
        }
    }
}
