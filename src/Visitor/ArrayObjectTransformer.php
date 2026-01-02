<?php

namespace App\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Arg;
use PhpParser\Node\Name;
use PhpParser\Node\Identifier;
use App\SymbolTable;

class ArrayObjectTransformer extends NodeVisitorAbstract {
  private SymbolTable $symbolTable;
  private $map = [
    'push'    => 'array_push',
    'pop'     => 'array_pop',
    'shift'   => 'array_shift',
    'unshift' => 'array_unshift',
    'keys'    => 'array_keys',
    'values'  => 'array_values',
    'count'   => 'count',
  ];

  public function __construct(SymbolTable $symbolTable) {
    $this->symbolTable = $symbolTable;
  }

  public function enterNode(Node $node) {
    // Sincroniza o escopo: entra na função na SymbolTable
    if (
      $node instanceof \PhpParser\Node\Stmt\Function_ ||
      $node instanceof \PhpParser\Node\Expr\Closure ||
      $node instanceof \PhpParser\Node\Expr\ArrowFunction
    ) {
      $this->symbolTable->enterScope();
    }
    return null;
  }

  public function leaveNode(Node $node) {
    // 1. Sincroniza o escopo: Sai do escopo ao terminar de processar uma função
    if (
      $node instanceof \PhpParser\Node\Stmt\Function_ ||
      $node instanceof \PhpParser\Node\Expr\Closure ||
      $node instanceof \PhpParser\Node\Expr\ArrowFunction
    ) {
      $this->symbolTable->exitScope();
      return $node;
    }

    // --- CASO ESPECIAL: .each() (Transformação de Expression para Statement) ---
    if ($node instanceof \PhpParser\Node\Stmt\Expression) {
      if ($node->expr instanceof MethodCall && $node->expr->name instanceof Identifier) {
        $methodName = $node->expr->name->toString();

        if ($methodName === 'each') {
          $methodCall = $node->expr;
          $arg = $methodCall->args[0]->value;

          if ($arg instanceof \PhpParser\Node\Expr\Closure || $arg instanceof \PhpParser\Node\Expr\ArrowFunction) {
            $params = $arg->params;

            // PHPScript: .each((valor, chave) => ...) -> PHP: foreach($array as $chave => $valor)
            $valVar = isset($params[0]) ? $params[0]->var : new \PhpParser\Node\Expr\Variable('val');
            $keyVar = isset($params[1]) ? $params[1]->var : null;

            $stmts = $arg instanceof \PhpParser\Node\Expr\ArrowFunction
              ? [new \PhpParser\Node\Stmt\Expression($arg->expr)]
              : $arg->stmts;

            // Substituímos a EXPRESSÃO inteira pelo FOREACH
            return new \PhpParser\Node\Stmt\Foreach_(
              $methodCall->var,
              $valVar,
              [
                'keyVar' => $keyVar,
                'stmts'  => $stmts
              ]
            );
          }
        }
      }
    }

    // --- TRANSFORMADORES DE EXPRESSÃO (join, map, push, etc.) ---
    if ($node instanceof MethodCall && $node->name instanceof Identifier) {
      $methodName = $node->name->toString();

      // Validação de Tipo via SymbolTable
      if ($node->var instanceof \PhpParser\Node\Expr\Variable) {
        $varName = $node->var->name;
        $type = $this->symbolTable->getType($varName, $node->var->getStartLine());

        if ($type !== 'ARRAY' && $type !== 'UNKNOWN' && $type !== null) {
          return null;
        }
      }

      // Lógica para .join() -> implode
      if ($methodName === 'join') {
        return new FuncCall(
          new Name('implode'),
          [
            $node->args[0] ?? new Arg(new \PhpParser\Node\Scalar\String_("")),
            new Arg($node->var)
          ]
        );
      }

      // Lógica para .map() -> array_map
      if ($methodName === 'map') {
        return new FuncCall(
          new Name('array_map'),
          [
            $node->args[0],
            new Arg($node->var)
          ]
        );
      }

      // Mapeamento PADRÃO (push, pop, etc.)
      if (isset($this->map[$methodName])) {
        $phpFunction = $this->map[$methodName];
        $args = [new Arg($node->var)];
        foreach ($node->args as $arg) {
          $args[] = $arg;
        }
        return new FuncCall(new Name($phpFunction), $args);
      }
    }

    return null;
  }
}
