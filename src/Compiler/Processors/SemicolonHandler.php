<?php

namespace PHPScript\Compiler\Processors;


class SemicolonHandler implements PreprocessorInterface {

  public function __construct(private ObjectsHandler $objectHandler)
  {
  }

  public function process(string $code): string {
    $lines = explode("\n", $code);
    $result = [];

    foreach ($lines as $line) {
      $trimmed = trim($line);

      // 1. Ignorar vazios, tags PHP ou linhas que já fecham blocos
      if ($trimmed === '}') {
        // Se a linha anterior (ou a estrutura) era uma atribuição de função, adiciona ;
        // Para simplificar, como no PHPScript quase tudo que fecha com } em nova linha
        // e não é um IF/ELSE pode levar ;, vamos testar:
        $result[] = $line . ';';
        continue;
      }

      if ($trimmed === '' || $trimmed === '<?php' || $trimmed === '}' || $trimmed === '{') {
        $result[] = $line;
        continue;
      }

      // 2. Se já tem ponto e vírgula ou abre bloco, não mexe
      if (str_ends_with($trimmed, ';') || str_ends_with($trimmed, '{')) {
        $result[] = $line;
        continue;
      }

      // 3. Ignorar declarações de estrutura (if, function, etc)
      if (preg_match('/^(function|func|if|else|for|while|foreach|try|catch|do)/i', $trimmed)) {
        $result[] = $line;
        continue;
      }

      // 4. Tratar comentários: coloca o ; antes do //
      if (str_contains($line, '//')) {
        $parts = explode('//', $line, 2);
        $content = rtrim($parts[0]);
        if ($content !== '') {
          $result[] = $content . ';' . ' //' . $parts[1];
        } else {
          $result[] = $line;
        }
        continue;
      }

      // 5. Para todo o resto (atribuições, chamadas de função, placeholders), coloca ;
      $result[] = $line . ';';
    }

    $code = implode("\n", $result);

    // ADICIONE ISSO AQUI:
    foreach ($this->objectHandler->getObjectPlaceholders() as $placeholder => $originalContent) {
      $code = str_replace($placeholder, $originalContent, $code);
    }
    return $code;
  }
}
