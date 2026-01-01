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
        // 1. Primeiro: Transforme chaves em arrays/objetos PHP
        // var obj = {id: 1}  =>  $obj = (object) ["id" => 1]
        $code = preg_replace('/(?<=\{|\,)\s*([a-zA-Z_]\w*)\s*:/', '"$1" =>', $code);
        $code = preg_replace('/\{/', '(object) [', $code);
        $code = str_replace('}', ']', $code);
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
        /**
         * BLOCO 2: TRADUÇÃO DE KEYWORDS E SINTAXE DE FUNÇÃO
         * Intenção: Suportar 'func' como apelido para 'function' e converter
         * a sintaxe de Arrow Function do PHPScript para o 'fn' do PHP.
         */
        $code = str_replace('func ', 'function ', $code);
        $code = preg_replace('/\((.*?)\)\s*=>/', 'fn($1) =>', $code);
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
            // Se deu match em comentário ou string (não existe grupo 1), retorna original
            if (!isset($matches[1]) || empty($matches[1])) {
                return $matches[0];
            }

            $word = $matches[1];

            // Se a palavra for reservada ou número, ignora
            if (in_array(strtolower($word), $reserved) || is_numeric($word)) {
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
        foreach ($lines as &$line) {
            $trimmed = trim($line);

            // Se a linha estiver vazia ou for apenas a tag do PHP, pula
            if ($trimmed === '' || $trimmed === '<?php') continue;

            // Se a linha já termina com caracteres de controle, pula
            if (str_ends_with($trimmed, '{') || str_ends_with($trimmed, '}') || str_ends_with($trimmed, ';')) {
                continue;
            }

            // --- LÓGICA PARA COMENTÁRIOS ---
            // Se a linha tem um comentário //, precisamos colocar o ; ANTES dele
            if (preg_match('/^(.*?)\s*(\/\/.*)$/', $line, $matches)) {
                $content = trim($matches[1]);
                $comment = $matches[2];

                // Só adiciona se o conteúdo não estiver vazio e não terminar com { } ou ;
                if ($content !== '' && !str_ends_with($content, '{') && !str_ends_with($content, '}') && !str_ends_with($content, ';')) {
                    $line = $content . ';' . ' ' . $comment;
                }
            } else {
                // Linha normal sem comentário
                $line .= ';';
            }
        }
        return implode("\n", $lines);
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
            $traverser->addVisitor(new \App\Visitor\VariableResolver());
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
