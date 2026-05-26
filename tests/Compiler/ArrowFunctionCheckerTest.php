<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Checker;
use PHireScript\Compiler\Checker\Declaration\ArrowFunction\ArrowFunctionChecker;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ArrowFunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\MethodScopeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ReturnTypeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ReturnNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Runtime\Exceptions\CheckerException;
use PHireScript\SymbolTable;

class ArrowFunctionCheckerTest extends TestCase
{
    private function makeToken(string $value = 'test'): Token
    {
        return new Token('T_IDENTIFIER', $value, 1, 1);
    }

    private function makeReturnType(string ...$types): ReturnTypeNode
    {
        return new ReturnTypeNode($this->makeToken(), $types);
    }

    private function makeBody(array $children = []): MethodScopeNode
    {
        $body = new MethodScopeNode($this->makeToken('{'));
        $body->children = $children;
        return $body;
    }

    private function makeReturnNode(bool $withValue = false): ReturnNode
    {
        $node = new ReturnNode($this->makeToken('return'));
        if ($withValue) {
            $node->expression = new VariableDeclarationNode($this->makeToken('x'), 'x');
        }
        return $node;
    }

    private function makeArrowFn(?ReturnTypeNode $returnType, ?MethodScopeNode $body): ArrowFunctionNode
    {
        $node = new ArrowFunctionNode($this->makeToken('('));
        $node->returnType = $returnType;
        $node->bodyCode = $body;
        return $node;
    }

    private function makeChecker(): Checker
    {
        return new Checker(new SymbolTable());
    }

    public function testVoidWithEmptyBodyPasses(): void
    {
        $checker = new ArrowFunctionChecker();
        $node = $this->makeArrowFn($this->makeReturnType('Void'), $this->makeBody([]));
        $checker->check($node, $this->makeChecker());
        $this->addToAssertionCount(1);
    }

    public function testNonVoidWithReturnPasses(): void
    {
        $checker = new ArrowFunctionChecker();
        $body = $this->makeBody([$this->makeReturnNode(true)]);
        $node = $this->makeArrowFn($this->makeReturnType('Int'), $body);
        $checker->check($node, $this->makeChecker());
        $this->addToAssertionCount(1);
    }

    public function testVoidWithBareReturnPasses(): void
    {
        $checker = new ArrowFunctionChecker();
        $body = $this->makeBody([$this->makeReturnNode(false)]);
        $node = $this->makeArrowFn($this->makeReturnType('Void'), $body);
        $checker->check($node, $this->makeChecker());
        $this->addToAssertionCount(1);
    }

    public function testNonVoidWithEmptyBodyThrows(): void
    {
        $this->expectException(CheckerException::class);
        $checker = new ArrowFunctionChecker();
        $node = $this->makeArrowFn($this->makeReturnType('Int'), $this->makeBody([]));
        $checker->check($node, $this->makeChecker());
    }

    public function testVoidWithValueReturnThrows(): void
    {
        $this->expectException(CheckerException::class);
        $checker = new ArrowFunctionChecker();
        $body = $this->makeBody([$this->makeReturnNode(true)]);
        $node = $this->makeArrowFn($this->makeReturnType('Void'), $body);
        $checker->check($node, $this->makeChecker());
    }

    public function testNonVoidWithNoReturnThrows(): void
    {
        $this->expectException(CheckerException::class);
        $checker = new ArrowFunctionChecker();
        $body = $this->makeBody([new VariableDeclarationNode($this->makeToken('x'), 'x')]);
        $node = $this->makeArrowFn($this->makeReturnType('String'), $body);
        $checker->check($node, $this->makeChecker());
    }
}
