<?php

namespace PHPScript;

use PHPScript\Compiler\Binder;
use PHPScript\Compiler\Checker;
use PHPScript\Compiler\Emitter;
use PHPScript\Compiler\Parser;
use PHPScript\Compiler\Processors\AccessorHandler;
use PHPScript\Compiler\Processors\FunctionBodyProcessor;
use PHPScript\Compiler\Processors\FunctionsHandler;
use PHPScript\Compiler\Processors\NativeTypesHandler;
use PHPScript\Compiler\Processors\ObjectsHandler;
use PHPScript\Compiler\Processors\PhpFileGeneratorHandler;
use PHPScript\Compiler\Processors\PhpFileHandler;
use PHPScript\Compiler\Processors\PreprocessorInterface;
use PHPScript\Compiler\Processors\ReturnTypeHandler;
use PHPScript\Compiler\Processors\SemicolonHandler;
use PHPScript\Compiler\Processors\VariablesBeforeInitializationHandler;
use PHPScript\Compiler\Processors\VariablesHandler;
use PHPScript\Compiler\Scanner;
use PHPScript\Helper\Debug\Debug;

class Transpiler
{
    private $preprocessors = [];
    private PreprocessorInterface $generator;
    private string $codeBeforeGenerator;

    public function __construct(private array $config)
    {

        $objectHandler = new ObjectsHandler();
        $this->preprocessors = [
            new PhpFileHandler(),
            $objectHandler,
            new NativeTypesHandler(),
            new VariablesHandler(),
            new FunctionsHandler(),
            new ReturnTypeHandler(),
            new FunctionBodyProcessor(),
            new AccessorHandler(),
            new VariablesBeforeInitializationHandler(),
            new SemicolonHandler($objectHandler),
        ];
        $this->generator = new PhpFileGeneratorHandler(false);
    }

    public function compile(string $code): string
    {
        try {
            $scanner = new Scanner($code);
            $tokens = $scanner->tokenize();

            $parser = new Parser();
            $ast = $parser->parse($tokens);
            //Debug::show($ast);exit;
            $symbolTable = new SymbolTable();
            $binder = new Binder($symbolTable);
            $result = $binder->bind($ast);

            $checker = new Checker();
            $checker->check($result, $symbolTable);
            //Debug::show($ast);exit;
            $emitter = new Emitter($this->config);
            $result = $emitter->emit($result);
            $this->codeBeforeGenerator = $result;
            $result = $this->generator->process($result);
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
