<?php

namespace PHPScript\Compiler\Processors;


class VariablesBeforeInitializationHandler implements PreprocessorInterface {

  public function process(string $code): string {
     /**
         * BLOCO 4: NORMALIZAÇÃO DE ATRIBUIÇÃO
         * Intenção: Capturar nomes de variáveis que estão sendo atribuídas (antes do '=')
         * ou acessadas (antes do '->') e garantir que tenham o '$'.
         * Ex: 'user = ...' vira '$user = ...'
         */
        //$code = preg_replace('/(?<![\$->])\b([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*(?==|->)/', '$$1', $code);
        // 1. Palavras que NUNCA devem receber '$'
        $reserved = [
            'string',
            'int',
            'float',
            'bool',
            'array',
            'object',
            'void', // Adicione estes!
            'php',
            'echo',
            'return',
            'if',
            'else',
            'foreach',
            'for',
            'while',
            'new',
            'null',
            'true',
            'false',
            'stdClass',
            'fn',
            'function'
        ];

        /**
         * Regex Ultra-Protegida:
         * 1. Ignora comentários de linha: \/\/.*
         * 2. Ignora comentários de bloco: \/\*[\s\S]*?\*\/
         * 3. Ignora strings: "..." ou '...'
         * 4. Ignora palavras dentro de parênteses de casting: (?<=\() (string|int|...) (?=\))
         * 5. Captura identificadores válidos
         */
        $pattern = '/(?:\/\/.*|\/\*[\s\S]*?\*\/|(?:"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'))|(?<!\$|->)\b([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\b(?!\s*\()/';
        $code = preg_replace_callback($pattern, function ($matches) use ($reserved) {
            if (!isset($matches[1]) || empty($matches[1])) {
                return $matches[0];
            }

            $word = $matches[1];

            // Se a palavra for reservada, número ou um placeholder de objeto, NÃO coloca $
            if (in_array(strtolower($word), $reserved) || is_numeric($word) || str_starts_with($word, '__OBJ_')) {
                return $word;
            }
            return '$' . $word;
        }, $code);

        // Correção final para a tag
        $code = preg_replace('/<\?\s*\$php/', '<?php', $code);
        $code = str_replace(['($bool)', '($int)', '($string)', '($array)', '($object)', '($float)'], ['(bool)', '(int)', '(string)', '(array)', '(object)', '(float)'], $code);
        return $code;
  }
}
