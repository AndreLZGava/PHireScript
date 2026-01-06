<?php

namespace PHPScript\Compiler;

use PHPScript\SymbolTable;
use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;

class Checker
{
    public function check(Program $ast, SymbolTable $table)
    {
        foreach ($ast->statements as $node) {
            if ($node instanceof ClassDefinition) {
                $this->checkClassBody($node, $table);
            }
        }
    }

    private function checkClassBody($classNode)
    {
        foreach ($classNode->body as $member) {
            if ($member instanceof PropertyDefinition) {
                if ($member->defaultValue !== null) {
                  // $this->ensureTypeCompatibility($member, $member->defaultValue);
                }
            }
        }
    }

    private function ensureTypeCompatibility(PropertyDefinition $prop, $valueNode)
    {
        $isValid = false;

        foreach ($prop->resolvedTypeInfo as $typeInfo) {
            if ($this->isCompatible($typeInfo, $valueNode)) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            throw new \Exception("Semantic Error: Property '{$prop->name}' does not support the assigned type.");
        }
    }

    private function isCompatible(array $typeInfo, $valueNode): bool
    {
      // Aqui você precisará de uma lógica que identifique o tipo do valor do nó
      // Por agora, vamos assumir uma verificação básica de categoria
        switch ($typeInfo['category']) {
            case 'primitive':
                return $this->checkPrimitive($typeInfo['native'], $valueNode);
            case 'supertype':
              // SuperTypes validam em Runtime, mas aqui podemos checar se o valor base é string
                return $this->checkPrimitive('string', $valueNode);
            case 'metatype':
              // MetaTypes geralmente aceitam strings ou números para inicializar
                return true;
            case 'custom':
              // Checar se o valor é uma instância da classe customizada
                return true;
            default:
                return false;
        }
    }

    private function checkPrimitive(string $nativeType, $valueNode): bool
    {
      // 1. Primeiro, precisamos descobrir qual é o tipo do nó que estamos recebendo
        $nodeType = $this->getNodeType($valueNode);

      // 2. Se o tipo do nó for 'unknown', o Checker não pode validar (ex: retorno de função desconhecida)
        if ($nodeType === 'unknown') {
            return true; // Ou você pode ser rigoroso e retornar false
        }

      // 3. Tabela de compatibilidade
        return match ($nativeType) {
            'string' => $nodeType === 'String',
            'int'    => $nodeType === 'Int',
            'float'  => $nodeType === 'Float' || $nodeType === 'Int', // Int pode entrar em Float
            'bool'   => $nodeType === 'Bool',
            'array'  => $nodeType === 'Array',
            'object' => $nodeType === 'Object' || $nodeType === 'Custom',
            default  => false
        };
    }

  /**
   * Método auxiliar para identificar o tipo de um nó da AST
   */
    private function getNodeType($node): string
    {
        if ($node instanceof \PHPScript\Compiler\Parser\Ast\LiteralNode) {
          // Se o seu LiteralNode guarda se é string ou int
            return $node->type;
        }

        if ($node instanceof \PHPScript\Compiler\Parser\Ast\VariableNode) {
          // Se for uma variável, buscamos na SymbolTable o tipo dela
          // para saber se ela pode ser atribuída à nova variável
            return $this->table->getType($node->name, $node->line);
        }

        return 'unknown';
    }
}
