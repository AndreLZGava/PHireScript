<?php

namespace PHPScript\Compiler\Processors;


class ReturnTypeHandler implements PreprocessorInterface {

  private string $arrayTypeRegex = '/(\w+\s*\(.*?\))\s*:\s*\[(.+?)\]/';
  public function process(string $code): string {
    // Regex para capturar assinaturas de função: nome(args): [Tipo|Tipo]
    // Captura 1: Nome e parênteses
    // Captura 2: O conteúdo dos colchetes (ex: User|Null)

    return preg_replace_callback($this->arrayTypeRegex, function ($matches) {
      $functionSignature = $matches[1]; // myFunction(String name)
      $rawInnerTypes = $matches[2];    // User|Null ou Ipv4|Ipv6

      // 1. Convertemos para o tipo nativo do PHP (array)
      // 2. Criamos uma marcação temporária (Placeholder) para que o
      //    BodyProcessor saiba que precisa injetar validação aqui.
      return $functionSignature . " : array /* @PS_VALIDATE_ARRAY[$rawInnerTypes] */";
    }, $code);
  }

  private function mapToNative(string $type): string {
    // Se for um dos nossos tipos especiais, o PHP nativo sempre verá como string ou mixed
    $specialTypes = ['Email', 'Ipv4', 'Ipv6', 'Json', 'Date'];

    if (in_array($type, $specialTypes)) {
      return 'string';
    }

    $map = ['Int' => 'int', 'String' => 'string', 'Float' => 'float', 'Bool' => 'bool'];
    return $map[$type] ?? $type;
  }
}
