<?php

namespace PHPScript\Visitor;

use PHPScript\SymbolTable;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Arg;
use PhpParser\Node\Name;

class StringObjectTransformer extends NodeVisitorAbstract {
    private $map = [
        'toUpperCase' => 'strtoupper',
        'toLowerCase' => 'strtolower',
        'length'      => 'strlen',
        'trim'        => 'trim',
        'contains'    => 'str_contains',
    ];

    private SymbolTable $symbolTable;

    public function __construct(SymbolTable $symbolTable) {
        $this->symbolTable = $symbolTable;
    }

    public function leaveNode(Node $node) {
        if ($node instanceof MethodCall) {
            $methodName = $node->name->toString();

            if (isset($this->map[$methodName])) {
                $phpFunction = $this->map[$methodName];
                return new FuncCall(
                    new Name($phpFunction),
                    [new Arg($node->var)]
                );
            }
        }
    }
}
