<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\NumberEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NumberNode;

class NumberEmitterTest extends EmitterTestCase
{
    public function testEmitsInteger(): void
    {
        $emitter = new NumberEmitter();
        $node = new NumberNode($this->makeToken('T_NUMBER', '42'), 42);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('42', $result);
    }

    public function testEmitsFloat(): void
    {
        $emitter = new NumberEmitter();
        $node = new NumberNode($this->makeToken('T_NUMBER', '3.14'), 3.14);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('3.14', $result);
    }

    public function testEmitsZero(): void
    {
        $emitter = new NumberEmitter();
        $node = new NumberNode($this->makeToken('T_NUMBER', '0'), 0);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('0', $result);
    }

    public function testSupportsNumberNode(): void
    {
        $emitter = new NumberEmitter();
        $node = new NumberNode($this->makeToken(), 1);

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
