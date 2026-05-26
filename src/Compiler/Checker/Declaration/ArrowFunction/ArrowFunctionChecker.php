<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Declaration\ArrowFunction;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ArrowFunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ReturnNode;
use PHireScript\Runtime\Exceptions\CheckerException;

#[CompilerPass(order: 8)]
class ArrowFunctionChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof ArrowFunctionNode;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        assert($node instanceof ArrowFunctionNode);

        $returnTypes = $node->returnType?->types ?? [];
        $isVoid = \count($returnTypes) === 1 && \strtolower((string) $returnTypes[0]) === 'void';
        $children = $node->bodyCode?->children ?? [];

        $returnNodes = \array_filter($children, fn ($c) => $c instanceof ReturnNode);
        $returnWithValue = \array_filter(
            $returnNodes,
            fn (ReturnNode $r) => $r->expression !== null
        );

        if (!$isVoid && $children === []) {
            throw new CheckerException(
                'Arrow function with return type "' . \implode('|', $returnTypes) . '" must have a body.',
                $node->line,
                $node->column
            );
        }

        if ($isVoid && $returnWithValue !== []) {
            throw new CheckerException(
                'Arrow function declared as Void cannot return a value.',
                $node->line,
                $node->column
            );
        }

        if (!$isVoid && $returnTypes !== [] && $returnNodes === []) {
            $type = \implode('|', $returnTypes);
            throw new CheckerException(
                'Arrow function with return type "' . $type . '" must contain a return statement.',
                $node->line,
                $node->column
            );
        }
    }
}
