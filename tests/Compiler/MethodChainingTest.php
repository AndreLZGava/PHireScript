<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Binder;
use PHireScript\Compiler\Checker;
use PHireScript\Compiler\Emitter;
use PHireScript\Compiler\Parser;
use PHireScript\Compiler\Scanner;
use PHireScript\Compiler\Program;
use PHireScript\Core\CompileMode;
use PHireScript\Core\CompilerContext;
use PHireScript\DependencyGraphBuilder;
use PHireScript\SymbolTable;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableReferenceNode;
use PHireScript\Runtime\Exceptions\CheckerException;

class MethodChainingTest extends TestCase
{
    private function compile(string $code): string
    {
        $config = ['namespace' => 'App', 'paths' => ['source' => 'samples']];
        $context = new CompilerContext(mode: CompileMode::DEBUG, inMemory: true);
        $depBuilder = new DependencyGraphBuilder();
        $symbolTable = new SymbolTable();

        $tokens = (new Scanner($code, 'test.phs'))->tokenize();
        $parser = new Parser(config: $config, dependencyBuilder: $depBuilder, context: $context);
        $program = $parser->parse($tokens, 'test.phs');

        (new Binder(new SymbolTable()))->bind($program);

        $checker = new Checker(new SymbolTable());
        $checker->check($program);

        $emitter = new Emitter(config: $config, dependencyManager: $depBuilder, symbolTable: $symbolTable);
        return $emitter->emit($program);
    }

    private function parse(string $code): Program
    {
        $config = ['namespace' => 'App', 'paths' => ['source' => 'samples']];
        $context = new CompilerContext(mode: CompileMode::DEBUG, inMemory: true);
        $depBuilder = new DependencyGraphBuilder();

        $tokens = (new Scanner($code, 'test.phs'))->tokenize();
        $parser = new Parser(config: $config, dependencyBuilder: $depBuilder, context: $context);
        return $parser->parse($tokens, 'test.phs');
    }

    // -------------------------------------------------------------------------
    // Parser: AST structure
    // -------------------------------------------------------------------------

    public function testSingleMethodChainProducesCorrectAst(): void
    {
        $program = $this->parse("mystring = 'hello'\nresult = mystring.length()\n");
        $stmt = $program->statements[1] ?? null;
        $this->assertInstanceOf(AssignmentNode::class, $stmt);
        $this->assertInstanceOf(FunctionNode::class, $stmt->right);
        $this->assertSame('length', $stmt->right->token->value);
        $this->assertInstanceOf(VariableReferenceNode::class, $stmt->right->variableBase);
    }

    public function testTwoMethodChainHasLinkedVariableBases(): void
    {
        $program = $this->parse("mystring = 'hello'\nresult = mystring.toUpperCase().length()\n");
        $stmt = $program->statements[1] ?? null;
        $this->assertInstanceOf(AssignmentNode::class, $stmt);
        $right = $stmt->right;
        $this->assertInstanceOf(FunctionNode::class, $right);
        $this->assertSame('length', $right->token->value);
        $this->assertInstanceOf(FunctionNode::class, $right->variableBase);
        $this->assertSame('toUpperCase', $right->variableBase->token->value);
    }

    public function testSafeNavigationTokenRecognized(): void
    {
        // ?. must appear after ) to be tokenized as T_SAFE_NAV (not part of an identifier)
        $scanner = new Scanner("result = mystring.between('a', 'b')?.length()\n", 'test.phs');
        $tokens = $scanner->tokenize();
        $safeNavToken = null;
        foreach ($tokens as $token) {
            if ($token->type === 'T_SAFE_NAV') {
                $safeNavToken = $token;
                break;
            }
        }
        $this->assertNotNull($safeNavToken, 'T_SAFE_NAV token should be produced by Scanner after )');
        $this->assertSame('?.', $safeNavToken->value);
    }

    // -------------------------------------------------------------------------
    // Emitter: PHP output
    // -------------------------------------------------------------------------

    public function testSingleMethodChainEmitsInlinePhp(): void
    {
        $php = $this->compile("mystring = 'hello world'\nresult = mystring.length()\n");
        $this->assertStringContainsString('$result = \strlen($mystring)', $php);
    }

    public function testTwoMethodChainEmitsNestedPhp(): void
    {
        $php = $this->compile("mystring = 'HELLO'\nresult = mystring.toLowerCase().length()\n");
        $this->assertStringContainsString('\strlen(\mb_strtolower($mystring', $php);
    }

    public function testThreeMethodChainEmitsThreeLevelNested(): void
    {
        $code = "mystring = 'hello world'\nresult = mystring.replace('hello', 'hi').toUpperCase().length()\n";
        $php = $this->compile($code);
        $this->assertStringContainsString('\strlen(\mb_strtoupper(\str_replace(', $php);
    }

    public function testLiteralChainEmitsWithLiteralAsBase(): void
    {
        $php = $this->compile("result = 'my string'.length()\n");
        $this->assertStringContainsString("\$result = \\strlen('my string')", $php);
    }

    public function testAutoAssignmentChain(): void
    {
        $php = $this->compile("mystring = 'hello'\nmystring = mystring.toUpperCase()\n");
        $this->assertStringContainsString('$mystring = \mb_strtoupper($mystring', $php);
    }

    public function testCrossTypeChainStringToInt(): void
    {
        $php = $this->compile("mystring = 'hello world'\nresult = mystring.split(' ').length()\n");
        $this->assertStringContainsString('\count(\explode(', $php);
    }

    public function testSafeNavigationEmitsNullGuard(): void
    {
        $php = $this->compile("mystring = 'hello world'\nresult = mystring.between('hello', 'world')?.length()\n");
        $this->assertStringContainsString('!== null', $php);
        $this->assertStringContainsString('$__chain_', $php);
    }

    public function testOriginalVariablePreservedInChainWithAssignment(): void
    {
        $php = $this->compile("mystring = 'hello'\nresult = mystring.toUpperCase()\n");
        // mystring should NOT be reassigned (result is assigned, not mystring)
        $lines = explode("\n", $php);
        $mystringReassigned = false;
        foreach ($lines as $line) {
            if (str_contains($line, '$mystring =') && str_contains($line, 'mb_strtoupper')) {
                $mystringReassigned = true;
            }
        }
        $this->assertFalse($mystringReassigned, '$mystring must not be reassigned when chain assigns to $result');
        $this->assertStringContainsString('$result = \mb_strtoupper($mystring', $php);
    }

    // -------------------------------------------------------------------------
    // Checker: error rules
    // -------------------------------------------------------------------------

    public function testCheckerThrowsOnNullableChainWithoutSafeNav(): void
    {
        $this->expectException(CheckerException::class);
        $this->expectExceptionMessageMatches('/may return.*Null/');
        $this->compile("mystring = 'hello world'\nresult = mystring.between('a', 'b').length()\n");
    }

    public function testCheckerAllowsNullableChainWithSafeNav(): void
    {
        $php = $this->compile("mystring = 'hello world'\nresult = mystring.between('hello', 'world')?.length()\n");
        $this->assertStringContainsString('$result', $php);
    }
}
