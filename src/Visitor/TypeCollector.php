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
        // Castings explícitos (ex: Object(...), String(...))
        if ($expr instanceof Node\Expr\Cast\Array_)  return 'ARRAY';
        if ($expr instanceof Node\Expr\Array_)  return 'ARRAY';

        if ($expr instanceof Node\Expr\Cast\Object_) return 'OBJECT';

        if ($expr instanceof Node\Expr\Cast\Bool_)   return 'BOOL';
        if ($expr instanceof Node\Expr\Cast\Int_)    return 'INT';
        if ($expr instanceof Node\Expr\Cast\String_) return 'STRING';
        if ($expr instanceof Node\Expr\Cast\Double)  return 'FLOAT';

        // Inferência por valor literal
        if ($expr instanceof Node\Expr\Array_) {
            // Se veio de {}, o transpiler converteu para Array,
            // mas o PHPScript trata como Object se houver chaves nomeadas
            return 'OBJECT';
        }

        if ($expr instanceof Node\Scalar\String_) return 'STRING';
        if ($expr instanceof Node\Scalar\LNumber) return 'INT';
        if ($expr instanceof Node\Scalar\DNumber) return 'FLOAT';

        return 'UNKNOWN';
    }
}
