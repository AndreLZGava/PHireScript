<?php

namespace PHPScript;

use PHPScript\Compiler\Binder;
use PHPScript\Compiler\Checker;
use PHPScript\Compiler\Emitter;
use PHPScript\Compiler\Parser;
use PHPScript\Compiler\Processors\PhpFileGeneratorHandler;
use PHPScript\Compiler\Processors\PreprocessorInterface;
use PHPScript\Compiler\Scanner;
use PHPScript\Compiler\Validator;
use PHPScript\Helper\Debug\Debug;

class Transpiler
{
    private PreprocessorInterface $generator;
    private string $codeBeforeGenerator;

    public function __construct(private array $config)
    {
        $this->generator = new PhpFileGeneratorHandler(false);
    }

    public function compile(string $code): string
    {
        try {
            $scanner = new Scanner($code);
            $tokens = $scanner->tokenize();

            $validator = new Validator();
            $validator->validate($tokens);

            $parser = new Parser();
            $ast = $parser->parse($tokens);

            $symbolTable = new SymbolTable();
            $binder = new Binder($symbolTable);
            $updatedAst = $binder->bind($ast);

            $checker = new Checker();
            $checker->check($updatedAst, $symbolTable);

            //Debug::show($ast);exit;
            $emitter = new Emitter($this->config);
            $preCompiledPhpCode = $emitter->emit($updatedAst);

            $this->codeBeforeGenerator = $preCompiledPhpCode;

            $result = $this->generator->process($preCompiledPhpCode);
            return $result;
        } catch (\Exception $e) {
            Debug::show($e->getMessage(), $e->getTraceAsString());
            exit;
        }
    }

    public function getCodeBeforeGenerator(): string
    {
        return $this->codeBeforeGenerator;
    }
}
