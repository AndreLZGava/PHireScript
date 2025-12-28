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

  public function leaveNode(Node $node) {
    if ($node instanceof MethodCall && $node->name instanceof Identifier) {
      $methodName = $node->name->toString();

      // 1. Get the variable name (e.g. 'frutas' in frutas.push())
      if ($node->var instanceof Node\Expr\Variable) {
        $varName = $node->var->name;

        // 2. Query the type in the SymbolTable
        $type = $this->symbolTable->get($varName);

        // 3. If we know it is NOT an array, we ignore this transformer
        // (The StringObjectTransformer will handle it if it's a string)
        if ($type !== 'ARRAY' && $type !== 'UNKNOWN' && $type !== null) {
          return null;
        }
      }

      // Join logic
      if ($methodName === 'join') {
        return new FuncCall(
          new Name('implode'),
          [$node->args[0] ?? new Arg(new Node\Scalar\String_("")), new Arg($node->var)]
        );
      }

      // Default mapping
      if (isset($this->map[$methodName])) {
        $phpFunction = $this->map[$methodName];
        $args = [new Arg($node->var)];
        foreach ($node->args as $arg) {
          $args[] = $arg;
        }
        return new FuncCall(new Name($phpFunction), $args);
      }
    }
  }
}
