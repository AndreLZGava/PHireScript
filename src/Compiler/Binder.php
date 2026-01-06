<?php

namespace PHPScript\Compiler;

use PHPScript\SymbolTable;
use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;

class Binder {
  private SymbolTable $globalTable;

  public function __construct(SymbolTable $symbolTable) {
    $this->globalTable = $symbolTable;
  }

  public function bind(Program $program) {
    // PASSAGEM 1: Registrar a existência de todas as classes
    // Isso permite que uma classe use outra como tipo, mesmo se definida depois
    foreach ($program->statements as $node) {
      if ($node instanceof ClassDefinition) {
        $this->globalTable->registerTypeDefinition($node->name, $node);
      }
    }

    // PASSAGEM 2: Resolver as propriedades e corpos
    foreach ($program->statements as $node) {
      if ($node instanceof ClassDefinition) {
        $this->bindClassBody($node);
      }
    }

    return $program;
  }

  protected function bindClassBody(ClassDefinition $class) {
    foreach ($class->body as $member) {
      if ($member instanceof PropertyDefinition) {
        $this->resolvePropertyTypes($member);
      }
      // Aqui você pode adicionar lógica para métodos (FunctionDefinition) no futuro
    }
  }

  protected function resolvePropertyTypes(PropertyDefinition $prop) {
    $typeString = $prop->type;
    $types = str_contains($typeString, '|') ? explode('|', $typeString) : [$typeString];

    $resolved = [];
    foreach ($types as $type) {
      $resolved[] = $this->categorizeType($type);
    }

    $prop->resolvedTypeInfo = $resolved;
  }

  protected function categorizeType(string $typeName): array {
    $primitives = [
      'String' => 'string',
      'Int'    => 'int',
      'Float'  => 'float',
      'Bool'   => 'bool',
      'Object' => 'object',
      'Array'  => 'array'
    ];

    if (isset($primitives[$typeName])) {
      return ['category' => 'primitive', 'native' => $primitives[$typeName]];
    }

    $metaTypes = ['Date', 'Currency', 'Phone'];
    if (in_array($typeName, $metaTypes)) {
      return ['category' => 'metatype', 'class' => "PHPScript\\Runtime\\Types\\MetaTypes\\$typeName"];
    }

    $superTypes = ['Email', 'Ipv4', 'Ipv6', 'Url'];
    if (in_array($typeName, $superTypes)) {
      return ['category' => 'supertype', 'class' => "PHPScript\\Runtime\\Types\\SuperTypes\\$typeName"];
    }

    // Se não for nada acima, verificamos se é uma classe que já registramos na Passagem 1
    $isRegistered = $this->globalTable->getTypeDefinition($typeName);

    return [
      'category' => $isRegistered ? 'custom' : 'unknown',
      'name' => $typeName
    ];
  }
}
