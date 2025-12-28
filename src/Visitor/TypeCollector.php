<?php

namespace App\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use App\SymbolTable;

class TypeCollector extends NodeVisitorAbstract {
    private SymbolTable $symbolTable;

    public function __construct(SymbolTable $symbolTable) {
        $this->symbolTable = $symbolTable;
    }

    public function enterNode(Node $node) {
        // When encountering an assignment: var x = ...
        if ($node instanceof Assign && $node->var instanceof Variable) {
            $varName = $node->var->name;
            $type = $this->inferType($node->expr);

            if ($type) {
                $this->symbolTable->set($varName, $type);
            }
        }
        return null;
    }

    private function inferType(Node $expr): ?string {
        // If it is "string" or 'string'
        if ($expr instanceof Node\Scalar\String_) return 'STRING';

        // If it is [1, 2, 3] or array()
        if ($expr instanceof Node\Expr\Array_) return 'ARRAY';

        // If it is 123 or 1.5
        if ($expr instanceof Node\Scalar\LNumber || $expr instanceof Node\Scalar\DNumber) return 'NUMBER';

        // If it is new stdClass()
        if ($expr instanceof Node\Expr\New_) return 'OBJECT';

        return 'UNKNOWN';
    }
}
