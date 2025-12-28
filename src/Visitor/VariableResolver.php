<?php

namespace App\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

class VariableResolver extends NodeVisitorAbstract {
    public function leaveNode(Node $node) {
        // 1. Common case: transform 'total' into '$total'
        if ($node instanceof Node\Expr\ConstFetch) {
            $name = $node->name->toString();
            $reserved = ['true', 'false', 'null'];
            if (!in_array(strtolower($name), $reserved)) {
                return new Variable($name);
            }
        }

        // 2. NEW: transform 'calcularTotal(...)' into '$calcularTotal(...)'
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            // List of native functions that should NOT become variables
            // If it is not native, we assume it is a variable holding a closure (arrow function)
            if (!function_exists($functionName)) {
                $node->name = new Variable($functionName);
            }
        }
    }
}
