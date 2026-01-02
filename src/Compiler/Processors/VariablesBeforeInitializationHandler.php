<?php

namespace PHPScript\Compiler\Processors;

class VariablesBeforeInitializationHandler implements PreprocessorInterface {

    public function process(string $code): string {
        $reserved = [
            'string', 'int', 'float', 'bool', 'array', 'object', 'void', 'php',
            'echo', 'return', 'if', 'else', 'foreach', 'for', 'while', 'new',
            'null', 'true', 'false', 'stdClass', 'fn', 'function', 'class', 'static',
            'public', 'protected', 'private', 'namespace', 'use', 'extends', 'implements'
        ];

        /**
         * Regex Atualizada:
         * Adicionamos Lookbehinds para evitar colocar $ em:
         * 1. (?<!class\s) -> Nomes de classes
         * 2. (?<!function\s) -> Nomes de funções
         * 3. (?<!new\s) -> Instanciação de objetos
         * 4. (?<!extends\s) -> Herança
         * 5. (?<!implements\s) -> Interfaces
         */
        $pattern = '/(?:\/\/.*|\/\*[\s\S]*?\*\/|(?:"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'))|(?<!\$|->|class\s|function\s|new\s|extends\s|implements\s|namespace\s|use\s)\b([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\b(?!\s*\()/i';

        $code = preg_replace_callback($pattern, function ($matches) use ($reserved) {
            // Se caiu na captura de comentários ou strings, retorna o original
            if (!isset($matches[1]) || empty($matches[1])) {
                return $matches[0];
            }

            $word = $matches[1];

            if (in_array(strtolower($word), $reserved) || is_numeric($word) || str_starts_with($word, '__OBJ_')) {
                return $word;
            }

            return '$' . $word;
        }, $code);

        // Limpeza de tags e casts
        $code = preg_replace('/<\?\s*\$php/', '<?php', $code);
        $code = str_replace(
            ['($bool)', '($int)', '($string)', '($array)', '($object)', '($float)'],
            ['(bool)', '(int)', '(string)', '(array)', '(object)', '(float)'],
            $code
        );

        return $code;
    }
}
