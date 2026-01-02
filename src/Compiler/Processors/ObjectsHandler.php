<?php

namespace PHPScript\Compiler\Processors;


class ObjectsHandler implements PreprocessorInterface {
  private $objectPlaceholders = [];

  public function setObjectPlaceholders($objectPlaceholders) {
    $this->objectPlaceholders = $objectPlaceholders;
  }

  public function getObjectPlaceholders() {
    return $this->objectPlaceholders;
  }

  public function process(string $code): string {
    // 1. Converte id: 1 para "id" => 1
    $code = preg_replace('/(?<==|^|\(|,)\s*\{\s*\}/', '(object) []', $code);
    $code = preg_replace('/(?<=\{|\,)\s*([a-zA-Z_]\w*)\s*:/', '"$1" =>', $code);

    // 2. Transforma objetos { } em [ ] e remove quebras de linha internas
    $pattern = '/\{([^{}]*?=>[^{}]*?)\}/s';
    while (preg_match($pattern, $code)) {
      $code = preg_replace_callback($pattern, function ($matches) {
        $content = str_replace(["\n", "\r"], " ", $matches[1]);
        return '[' . $content . ']';
      }, $code);
    }

    // 3. PROTEÇÃO: Identifica atribuições de objetos/arrays e as esconde
    // Ex: $config = [ ... ]; vira $config = __OBJ_0__;
    $code = preg_replace_callback('/=\s*(\[(?:[^\[\]]|(?R))*\])/s', function ($matches) {
      $placeholder = "__OBJ_" . count($this->objectPlaceholders) . "__";
      $this->objectPlaceholders[$placeholder] = $matches[1];
      return "= " . $placeholder;
    }, $code);

    return $code;
  }
}
