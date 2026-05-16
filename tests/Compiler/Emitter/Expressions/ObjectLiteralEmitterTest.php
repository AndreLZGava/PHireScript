<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\ObjectLiteralEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ObjectLiteralNode;

class ObjectLiteralEmitterTest extends EmitterTestCase
{
    public function testEmitsEmptyObjectLiteral(): void
    {
        $emitter = new ObjectLiteralEmitter();
        $node = new ObjectLiteralNode($this->makeToken('T_SYMBOL', '{'), []);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('(object) []', $result);
    }

    public function testSupportsObjectLiteralNode(): void
    {
        $emitter = new ObjectLiteralEmitter();
        $node = new ObjectLiteralNode($this->makeToken(), []);

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
