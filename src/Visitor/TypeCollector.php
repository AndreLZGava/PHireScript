<?php

declare(strict_types=1);

namespace PHireScript\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PHireScript\SymbolTable;

class TypeCollector extends NodeVisitorAbstract
{
    public function __construct(private readonly SymbolTable $symbolTable)
    {
    }

    public function enterNode(Node $node)
    {
        if (
            $node instanceof \PhpParser\Node\Stmt\Function_ ||
            $node instanceof \PhpParser\Node\Expr\Closure ||
            $node instanceof \PhpParser\Node\Expr\ArrowFunction
        ) {
            if ($node instanceof \PhpParser\Node\Stmt\Function_) {
                $this->symbolTable->registerFunction($node->name->toString());
            }
            $this->symbolTable->enterScope();

            foreach ($node->params as $param) {
                if ($param->var instanceof \PhpParser\Node\Expr\Variable && is_string($param->var->name)) {
                    if ($param->type instanceof \PhpParser\Node\UnionType) {
                        $type = implode('|', array_map(
                            fn ($t) => $t instanceof \PhpParser\Node\Identifier
                                || $t instanceof \PhpParser\Node\Name
                                ? (string) $t
                                : 'UNKNOWN',
                            $param->type->types
                        ));
                    } elseif (
                        $param->type instanceof \PhpParser\Node\Identifier
                        || $param->type instanceof \PhpParser\Node\Name
                    ) {
                        $type = (string) $param->type;
                    } else {
                        $type = 'UNKNOWN';
                    }
                    $this->symbolTable->setType($param->var->name, strtoupper($type));
                }
            }
        }

        if ($node instanceof Assign && $node->var instanceof Variable && is_string($node->var->name)) {
            $varName = $node->var->name;
            $type = $this->inferType($node->expr);
            if ($type) {
                $this->symbolTable->setType($varName, $type);
            }
        }
        return null;
    }

    public function leaveNode(\PhpParser\Node $node)
    {
        if (
            $node instanceof \PhpParser\Node\Stmt\Function_ ||
            $node instanceof \PhpParser\Node\Expr\Closure ||
            $node instanceof \PhpParser\Node\Expr\ArrowFunction
        ) {
            $this->symbolTable->exitScope();
        }
    }

    private function inferType(Node $expr): ?string
    {
        if ($expr instanceof Node\Expr\Cast\Array_) {
            return 'ARRAY';
        }
        if ($expr instanceof Node\Expr\Array_) {
            return 'ARRAY';
        }

        if ($expr instanceof Node\Expr\Cast\Object_) {
            return 'OBJECT';
        }

        if ($expr instanceof Node\Expr\Cast\Bool_) {
            return 'BOOL';
        }
        if ($expr instanceof Node\Expr\Cast\Int_) {
            return 'INT';
        }
        if ($expr instanceof Node\Expr\Cast\String_) {
            return 'STRING';
        }
        if ($expr instanceof Node\Expr\Cast\Double) {
            return 'FLOAT';
        }

        if ($expr instanceof Node\Scalar\String_) {
            return 'STRING';
        }
        if ($expr instanceof Node\Scalar\LNumber) {
            return 'INT';
        }
        if ($expr instanceof Node\Scalar\DNumber) {
            return 'FLOAT';
        }

        return 'UNKNOWN';
    }
}
