<?php

namespace App;

use App\Visitor\StringObjectTransformer;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter;
use App\Visitor\VariableResolver;

class Transpiler {
    private $parser;
    private $printer;
    private $objectPlaceholders = [];

    public function __construct() {
        $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
        $this->printer = new PrettyPrinter\Standard();
    }

    private function shouldBeValidPhpFile($code) {
        // Garante que o código gerado seja um arquivo PHP válido para o Parser.
        if (!str_starts_with(trim($code), '<?php')) {
            $code = "<?php\n" . $code;
        }
        return $code;
    }

    private function handleObjects($code) {
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

    private function handleNativePhpTypes($code) {
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

    private function convertVariables($code) {
        /**
         * BLOCO 1: NORMALIZAÇÃO DE IDENTIFICADORES E PARÂMETROS
         * Intenção: Garantir que variáveis recebam o prefixo '$'.
         * O desafio aqui é NÃO colocar '$' em números, strings ou variáveis que já possuem o prefixo.
         * Atualmente, ele tenta filtrar expressões matemáticas para evitar o erro de '$1'.
         */
        $code = preg_replace_callback('/\((.*?)\)/', function ($matches) {
            $params = explode(',', $matches[1]);
            $fixedParams = array_map(function ($param) {
                $p = trim($param);
                if (
                    empty($p) ||
                    str_starts_with($p, '$') ||
                    str_contains($p, '"') ||
                    str_contains($p, "'") ||
                    is_numeric($p) ||
                    preg_match('/^[^a-zA-Z_\x7f-\xff]/', $p)
                ) {
                    return $param;
                }
                return '$' . $p;
            }, $params);
            return '(' . implode(', ', $fixedParams) . ')';
        }, $code);

        return $code;
    }

    private function handleFunctions($code) {
        // 1. Arrow Functions e Closures
        // Adicionamos o lookbehind (?<!->) para garantir que não estamos
        // transformando algo que seja parte de um método
        $code = preg_replace_callback('/(?<!->)\((.*?)\)\s*=>\s*(\{?)/', function ($matches) {
            $params = $matches[1];
            $hasBrace = ($matches[2] === '{');
            return $hasBrace ? "function($params) {" : "fn($params) => ";
        }, $code);
        // 2. Shorthand: nome() { => function nome() {
        // AQUI É A CHAVE: O (?<!->) impede "->each(" de virar "->function each("
        $pattern = '/(?<!function|func|if|while|for|foreach|switch|catch|return|->)\b([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\((.*?)\)\s*\{/s';
        $code = preg_replace($pattern, 'function $1($2) {', $code);

        // 3. Limpeza de segurança
        $code = str_replace('function eachfunction', 'each', $code);
        $code = preg_replace('/each\s*\(\s*\((.*?)\)\s*\{/', 'each(function($1) {', $code);
        $code = str_replace('function fn(', 'fn(', $code);
        return $code;
    }

    private function handleAcessors($code) {
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

    private function handleVariablesBeforeInitialization($code) {
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

    private function addSemicolon($code) {
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
        foreach ($this->objectPlaceholders as $placeholder => $originalContent) {
            $code = str_replace($placeholder, $originalContent, $code);
        }
        // Limpa para a próxima execução
        $this->objectPlaceholders = [];

        return $code;
    }

    private function formatAsPhpFile($code) {
        /**
         * BLOCO 6: PROCESSAMENTO DO AST (ABSTACT SYNTAX TREE)
         * Intenção: Agora que o código "parece" PHP, o parser entra em ação.
         * 1. Transforma o código em uma estrutura de árvore (AST).
         * 2. TypeCollector: Mapeia quais variáveis são de quais tipos (Symbol Table).
         * 3. Transformers: Substituem chamadas de métodos de String/Array por funções PHP nativas.
         * 4. Printer: Converte a árvore de volta para texto PHP limpo e formatado.
         */
        try {
            $ast = $this->parser->parse($code);
            $symbolTable = new \App\SymbolTable();

            // Passagem 1: Coleta de tipos
            $collector = new \PhpParser\NodeTraverser();
            $collector->addVisitor(new \App\Visitor\TypeCollector($symbolTable));
            $ast = $collector->traverse($ast);

            // Passagem 2: Resolução de métodos e variáveis
            $traverser = new \PhpParser\NodeTraverser();
            $traverser->addVisitor(new \App\Visitor\VariableResolver($symbolTable));
            $traverser->addVisitor(new \App\Visitor\StringObjectTransformer($symbolTable));
            $traverser->addVisitor(new \App\Visitor\ArrayObjectTransformer($symbolTable));

            $ast = $traverser->traverse($ast);

            return $this->printer->prettyPrintFile($ast);
        } catch (\PhpParser\Error $error) {
            // Se o código gerado pelas Regexes acima estiver inválido, o erro estoura aqui.
            echo "--- DEBUG (Generated Code) ---\n" . $code . "\n";
            throw new \Exception("Compilation error: " . $error->getMessage());
        }
    }

    public function compile(string $code): string {
        $code = $this->shouldBeValidPhpFile($code);
        $code = $this->handleObjects($code);
        $code = $this->handleNativePhpTypes($code);
        $code = $this->convertVariables($code);
        $code = $this->handleFunctions($code);
        $code = $this->handleAcessors($code);
        $code = $this->handleVariablesBeforeInitialization($code);
        $code = $this->addSemicolon($code);
        return $this->formatAsPhpFile($code);
    }
}
