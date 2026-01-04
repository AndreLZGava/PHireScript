<?php

namespace PHPScript;

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
use PHPScript\Compiler\Scanner\Lexer;
use PHPScript\Compiler\Scanner\Parser;
use PHPScript\Compiler\Scanner\Parser2;
use PHPScript\Helper\Debug\Debug;

class Transpiler {
    private $preprocessors = [];
    private PreprocessorInterface $generator;
    private string $codeBeforeGenerator;

    public function __construct(private bool $debugMode = false) {
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
        $this->generator = new PhpFileGeneratorHandler($debugMode);
    }

    public function compile(string $code): string {
        if (true) {
            try {

                $lexer = new Lexer($code);
                $tokens = $lexer->tokenize();

                $parser = new Parser2();
                $ast = $parser->parse($tokens);

                print_r($ast);
                exit;
            } catch (\Exception $e) {
                Debug::show($e->getMessage(), $e->getTraceAsString());
                exit;
            }
        }

        //Debug::show(token_get_all($code, TOKEN_PARSE));exit;
        foreach ($this->preprocessors as $processor) {
            //$code = $processor->process($code);
            //Debug::show(get_class($processor), $code);
        }
        //$this->codeBeforeGenerator = $code;
        //return $this->generator->process($code);
    }

    public function getCodeBeforeGenerator(): string {
        return '';
        $this->codeBeforeGenerator;
    }
}
