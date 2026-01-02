<?php

namespace PHPScript\Compiler\Processors;


class AccessorHandler implements PreprocessorInterface {

  public function process(string $code): string {
    /**
     * BLOCO 3: ACESSO A MEMBROS E CONCATENAÇÃO
     * Intenção:
     * 1. Trocar o ponto '.' (acesso a objeto em JS/TS) por '->' (PHP).
     * 2. Trocar o '+' por '.' quando usado entre aspas (concatenação de strings).
     * 3. Remover a keyword 'var' (PHP usa apenas a atribuição direta).
     */
    $code = preg_replace('/(?<!\d)\.|\.(?!\d)/', '->', $code);
    $code = preg_replace('/(["\'])\s*\+\s*|\s*\+\s*(["\'])/', '$1 . $2', $code);
    $code = str_replace('var ', '', $code);
    return $code;
  }
}
