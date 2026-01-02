<?php

namespace PHPScript\Compiler\Processors;


class NativeTypesHandler implements PreprocessorInterface {

  public function process(string $code): string {
    /**
     * BLOCO 0: MAPEAMENTO DE TIPOS MODERNOS
     * Intenção: Traduzir a sintaxe amigável do PHPScript para tipos nativos do PHP.
     * 1. Transforma Bool(x) em (bool)(x) - Casting explícito.
     * 2. Transforma retornos de função ': Bool' em ': bool' - Type Hinting.
     */
    $typeMap = [
      'Bool'   => 'bool',
      'Int'    => 'int',
      'Float'  => 'float',
      'String' => 'string',
      'Array'  => 'array',
      'Object' => 'object',
      'Void'   => 'void'
    ];

    foreach ($typeMap as $psType => $phpType) {
      // USE ASPAS SIMPLES AQUI: '(' . $phpType . ')($1)'
      // Se usar aspas duplas, o PHP tenta ler $phpType como variável.
      $code = preg_replace('/\b' . $psType . '\s*\((.*?)\)/', '(' . $phpType . ')($1)', $code);
    }

    foreach ($typeMap as $psType => $phpType) {
      $code = preg_replace('/:\s*' . $psType . '\b/', ': ' . $phpType, $code);
    }

    return $code;
  }
}
