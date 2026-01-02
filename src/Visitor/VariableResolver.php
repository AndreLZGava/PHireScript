<?php

namespace PHPScript\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPScript\SymbolTable; // Certifique-se de importar sua SymbolTable

class VariableResolver extends NodeVisitorAbstract {
    private $symbolTable;

    public function __construct(SymbolTable $symbolTable) {
        $this->symbolTable = $symbolTable;
    }

    public function leaveNode(Node $node) {
        // 1. Caso comum: transformar identificadores em variáveis
        if ($node instanceof Node\Expr\ConstFetch) {
            $name = $node->name->toString();
            $reserved = ['true', 'false', 'null'];
            if (!in_array(strtolower($name), $reserved)) {
                return new Variable($name);
            }
        }

        // 2. Chamadas de Função Inteligentes
        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            // Se o nome NÃO é uma função nativa...
            if (!function_exists($functionName)) {

                // ...E se ele NÃO foi registrado como uma função real na nossa SymbolTable
                // Assumimos que ele deve ser uma variável contendo uma closure.
                if (!$this->symbolTable->isFunction($functionName)) {
                    $node->name = new Variable($functionName);
                }
                // Se isFunction for true, retornamos o original (chamada sem $)
            }
        }
    }
}
