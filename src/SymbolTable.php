<?php

namespace PHPScript;

class SymbolTable {
    private array $scopes = [[]]; // Começa com o escopo global

    private array $functionReturns = [];

    private $functions = [];

    public function __construct() {
        $this->registerBuiltins();
    }

    public function enterScope() {
        array_push($this->scopes, []);
    }

    public function getAllScopes() {
        return ($this->scopes);
    }

    public function exitScope() {
        array_pop($this->scopes);
    }

    public function setType($name, $type, $linePosition) {
        // Define sempre no escopo atual (o topo da pilha)
        $this->scopes[$name][$linePosition] = $type;
    }

    public function getType($name, $linePosition) {
        // Procura do escopo mais interno para o mais externo
        for ($i = count($this->scopes) - 1; $i >= 0; $i--) {
            if (isset($this->scopes[$name][$linePosition])) {
                return $this->scopes[$name][$linePosition];
            }
        }
        return 'UNKNOWN';
    }

    public function registerBuiltins() {
        // Mapeamos que certas funções/métodos sempre retornam STRING
        $this->functionReturns = [
            'toUpperCase' => 'STRING',
            'toLowerCase' => 'STRING',
            'join'        => 'STRING',
            'push'        => 'ARRAY', // array_push retorna int, mas para o PS tratamos como mutação de array
        ];
    }

    public function registerFunction($name) {
        $this->functions[$name] = true;
    }

    public function isFunction($name) {
        return isset($this->functions[$name]);
    }
}
