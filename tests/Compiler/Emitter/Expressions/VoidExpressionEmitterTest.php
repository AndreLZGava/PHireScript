<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\VoidExpressionEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\VoidExpressionNode;

class VoidExpressionEmitterTest extends EmitterTestCase
{
    public function testEmitsEmptyString(): void
    {
        $emitter = new VoidExpressionEmitter();
        $node = new VoidExpressionNode($this->makeToken('T_PRIMITIVE', 'Void'));

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('', $result);
    }

    public function testSupportsVoidExpressionNode(): void
    {
        $emitter = new VoidExpressionEmitter();
        $node = new VoidExpressionNode($this->makeToken('T_PRIMITIVE', 'Void'));

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
