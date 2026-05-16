<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\NullEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NullNode;

class NullEmitterTest extends EmitterTestCase
{
    public function testEmitsNull(): void
    {
        $emitter = new NullEmitter();
        $node = new NullNode($this->makeToken('T_NULL', 'null'));

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('null', $result);
    }

    public function testSupportsNullNode(): void
    {
        $emitter = new NullEmitter();
        $node = new NullNode($this->makeToken());

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
