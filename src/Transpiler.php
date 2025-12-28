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

    public function compile(string $code): string {
        if (!str_starts_with(trim($code), '<?php')) {
            $code = "<?php\n" . $code;
        }

        // --- STEP 1: Parameter and Identifier Normalization ---

        // C) Parameter Normalization (Moved up and with number filtering)
        // This prevents transforming (1 + rate) into ($1 + $rate)
        $code = preg_replace_callback('/\((.*?)\)/', function ($matches) {
            $params = explode(',', $matches[1]);
            $fixedParams = array_map(function ($param) {
                $p = trim($param);
                // Only adds $ if: it is not a number, has no quotes,
                // does not start with $, and is not empty
                if (
                    empty($p) ||
                    str_starts_with($p, '$') ||
                    str_contains($p, '"') ||
                    str_contains($p, "'") ||
                    is_numeric($p) || // <--- PREVENTS THE $1 ERROR
                    preg_match('/^[^a-zA-Z_\x7f-\xff]/', $p) // Prevents starting with symbols
                ) {
                    return $param;
                }
                return '$' . $p;
            }, $params);
            return '(' . implode(', ', $fixedParams) . ')';
        }, $code);

        // A) New Features: func and Arrow Functions
        $code = str_replace('func ', 'function ', $code);
        $code = preg_replace('/\((.*?)\)\s*=>/', 'fn($1) =>', $code);

        // B) Access and Concatenation Transformations
        $code = preg_replace('/(?<!\d)\.|\.(?!\d)/', '->', $code);
        $code = preg_replace('/(["\'])\s*\+\s*|\s*\+\s*(["\'])/', '$1 . $2', $code);
        $code = str_replace('var ', '', $code);

        // --- STEP 2: Assignment Normalization ---
        $code = preg_replace('/(?<![\$->])\b([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*(?==|->)/', '$$1', $code);

        // --- STEP 3: ASI (Semicolons) ---
        $lines = explode("\n", $code);
        foreach ($lines as &$line) {
            $trimmed = trim($line);
            if (
                $trimmed !== '' && $trimmed !== '<?php' &&
                !str_ends_with($trimmed, '{') && !str_ends_with($trimmed, '}') &&
                !str_ends_with($trimmed, ';')
            ) {
                $line .= ';';
            }
        }
        $code = implode("\n", $lines);

        try {
            $ast = $this->parser->parse($code);
            $symbolTable = new \App\SymbolTable();

            // STEP A: Collect types (First pass)
            $collector = new \PhpParser\NodeTraverser();
            $collector->addVisitor(new \App\Visitor\TypeCollector($symbolTable));
            $ast = $collector->traverse($ast);

            // STEP B: Transform (Second pass)
            $traverser = new \PhpParser\NodeTraverser();
            $traverser->addVisitor(new \App\Visitor\VariableResolver());

            // We pass the SymbolTable to the Transformers!
            $traverser->addVisitor(new \App\Visitor\StringObjectTransformer($symbolTable));
            $traverser->addVisitor(new \App\Visitor\ArrayObjectTransformer($symbolTable));

            $ast = $traverser->traverse($ast);

            return $this->printer->prettyPrintFile($ast);
        } catch (\PhpParser\Error $error) {
            echo "--- DEBUG (Generated Code) ---\n" . $code . "\n";
            throw new \Exception("Compilation error: " . $error->getMessage());
        }
    }
}
