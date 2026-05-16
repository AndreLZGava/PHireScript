<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\ArrayLiteralEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ArrayLiteralNode;

class ArrayLiteralEmitterTest extends EmitterTestCase
{
    public function testEmitsEmptyArray(): void
    {
        $emitter = new ArrayLiteralEmitter();
        $node = new ArrayLiteralNode($this->makeToken('T_SYMBOL', '['), []);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertStringContainsString('[', $result);
        $this->assertStringContainsString(']', $result);
    }

    public function testEmptyArrayOutputFormat(): void
    {
        $emitter = new ArrayLiteralEmitter();
        $node = new ArrayLiteralNode($this->makeToken('T_SYMBOL', '['), []);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame("[\n\n]", $result);
    }

    public function testSupportsArrayLiteralNode(): void
    {
        $emitter = new ArrayLiteralEmitter();
        $node = new ArrayLiteralNode($this->makeToken(), []);

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
