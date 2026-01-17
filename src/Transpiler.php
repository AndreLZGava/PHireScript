<?php

declare(strict_types=1);

namespace PHPScript;

use PHPScript\Compiler\Binder;
use PHPScript\Compiler\Checker;
use PHPScript\Compiler\Emitter;
use PHPScript\Compiler\Parser;
use PHPScript\Compiler\Processors\PhpFileGeneratorHandler;
use PHPScript\Compiler\Processors\PreprocessorInterface;
use PHPScript\Compiler\Scanner;
use PHPScript\Compiler\Validator;
use PHPScript\Core\CompilerContext;
use PHPScript\Helper\Debug\Debug;
use Symfony\Component\DependencyInjection\Compiler\Compiler;

class Transpiler implements TranspilerInterface
{
    private readonly PreprocessorInterface $generator;
    private string $codeBeforeGenerator;

    public function __construct(
        private readonly array $config,
        private DependencyGraphBuilder $dependencyManager,
    ) {
        $this->generator = new PhpFileGeneratorHandler(false);
    }

    public function compile(string $code, string $path): string
    {
        $scanner = new Scanner($code);
        $tokens = $scanner->tokenize();

        //Debug::show($tokens);exit;
        $validator = new Validator();
        $validator->validate($tokens);

        $parser = new Parser($this->config);
        $ast = $parser->parse($tokens, $path);
        //Debug::show($ast);exit;
        $symbolTable = new SymbolTable();
        $binder = new Binder($symbolTable);
        $updatedAst = $binder->bind($ast);

        $checker = new Checker();
        $checker->check($updatedAst, $symbolTable);

        //Debug::show($updatedAst);exit;
        $emitter = new Emitter($this->config, $this->dependencyManager);
        $preCompiledPhpCode = $emitter->emit($updatedAst);

        $this->codeBeforeGenerator = $preCompiledPhpCode;

        $result = $this->generator->process($preCompiledPhpCode);

        return $result;
    }

    public function getCodeBeforeGenerator(): string
    {
        return $this->codeBeforeGenerator;
    }
}
