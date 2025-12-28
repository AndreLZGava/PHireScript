<?php

namespace App\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;

class VariableFixer extends NodeVisitorAbstract {
    public function leaveNode(Node $node) {
        // If the regex added $ to something that is actually a property or method name,
        // the parser may have created a Variable node where an Identifier should exist.

        // Example: Fix $user->$name to $user->name
        if ($node instanceof Node\Expr\PropertyFetch && $node->name instanceof Variable) {
            $node->name = new Identifier($node->name->name);
        }

        // Fix $new $stdClass to new stdClass
        if ($node instanceof Node\Expr\New_ && $node->class instanceof Variable) {
            $node->class = new Node\Name($node->class->name);
        }
    }
}
