<?php

namespace PHPScript\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPScript\SymbolTable;

class VariableResolver extends NodeVisitorAbstract
{
    private $symbolTable;

    public function __construct(SymbolTable $symbolTable)
    {
        $this->symbolTable = $symbolTable;
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\ConstFetch) {
            $name = $node->name->toString();
            $reserved = ['true', 'false', 'null'];
            if (!in_array(strtolower($name), $reserved)) {
                return new Variable($name);
            }
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            if (!function_exists($functionName)) {
                if (!$this->symbolTable->isFunction($functionName)) {
                    $node->name = new Variable($functionName);
                }
            }
        }
    }
}
