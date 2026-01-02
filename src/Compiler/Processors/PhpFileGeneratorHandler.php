<?php

namespace PHPScript\Compiler\Processors;

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;

class PhpFileGeneratorHandler implements PreprocessorInterface {
  private $parser;
  private $printer;
  public function __construct(private bool $strictDebugMode = false) {
    $this->parser = (new ParserFactory())->createForNewestSupportedVersion();
    $this->printer = new PrettyPrinter\Standard();
  }
  public function process(string $code): string {
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
      $symbolTable = new \PHPScript\SymbolTable();

      // Passagem 1: Coleta de tipos
      $collector = new \PhpParser\NodeTraverser();
      $collector->addVisitor(new \PHPScript\Visitor\TypeCollector($symbolTable));
      $ast = $collector->traverse($ast);

      // Passagem 2: Resolução de métodos e variáveis
      $traverser = new \PhpParser\NodeTraverser();
      $traverser->addVisitor(new \PHPScript\Visitor\VariableResolver($symbolTable));
      $traverser->addVisitor(new \PHPScript\Visitor\StringObjectTransformer($symbolTable));
      $traverser->addVisitor(new \PHPScript\Visitor\ArrayObjectTransformer($symbolTable));

      $ast = $traverser->traverse($ast);

      return $this->printer->prettyPrintFile($ast);
    } catch (\PhpParser\Error $error) {
      if (!$this->strictDebugMode) {
        // Se o código gerado pelas Regexes acima estiver inválido, o erro estoura aqui.
        echo "--- DEBUG (Generated Code) ---\n" . $code . "\n";
        throw new \Exception("Compilation error: " . $error->getMessage());
      }
      throw new \Exception("Compilation error: " . $error->getMessage());
    }
  }
}
