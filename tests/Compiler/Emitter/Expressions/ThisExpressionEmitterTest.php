<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\ThisExpressionEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ThisExpressionNode;

class ThisExpressionEmitterTest extends EmitterTestCase
{
    public function testEmitsThis(): void
    {
        $emitter = new ThisExpressionEmitter();
        $node = new ThisExpressionNode($this->makeToken('T_KEYWORD', 'this'));

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('$this', $result);
    }

    public function testSupportsThisExpressionNode(): void
    {
        $emitter = new ThisExpressionEmitter();
        $node = new ThisExpressionNode($this->makeToken('T_KEYWORD', 'this'));

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
