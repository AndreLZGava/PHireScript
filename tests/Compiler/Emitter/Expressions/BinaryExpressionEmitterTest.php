<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\BinaryExpressionEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\BinaryExpressionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NumberNode;

class BinaryExpressionEmitterTest extends EmitterTestCase
{
    public function testEmitsAddition(): void
    {
        $emitter = new BinaryExpressionEmitter();
        $left = new NumberNode($this->makeToken('T_NUMBER', '1'), 1);
        $right = new NumberNode($this->makeToken('T_NUMBER', '2'), 2);
        $node = new BinaryExpressionNode($left, '+', $right);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('1 + 2', $result);
    }

    public function testEmitsSubtraction(): void
    {
        $emitter = new BinaryExpressionEmitter();
        $left = new NumberNode($this->makeToken('T_NUMBER', '10'), 10);
        $right = new NumberNode($this->makeToken('T_NUMBER', '3'), 3);
        $node = new BinaryExpressionNode($left, '-', $right);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('10 - 3', $result);
    }

    public function testEmitsComparison(): void
    {
        $emitter = new BinaryExpressionEmitter();
        $left = new NumberNode($this->makeToken('T_NUMBER', '5'), 5);
        $right = new NumberNode($this->makeToken('T_NUMBER', '5'), 5);
        $node = new BinaryExpressionNode($left, '===', $right);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('5 === 5', $result);
    }

    public function testSupportsBinaryExpressionNode(): void
    {
        $emitter = new BinaryExpressionEmitter();
        $left = new NumberNode($this->makeToken('T_NUMBER', '1'), 1);
        $right = new NumberNode($this->makeToken('T_NUMBER', '2'), 2);
        $node = new BinaryExpressionNode($left, '+', $right);

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
