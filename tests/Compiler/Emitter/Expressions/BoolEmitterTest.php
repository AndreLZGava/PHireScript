<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\BoolEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\BoolNode;

class BoolEmitterTest extends EmitterTestCase
{
    public function testEmitsTrue(): void
    {
        $emitter = new BoolEmitter();
        $node = new BoolNode($this->makeToken('T_BOOL', 'true'), true);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('true', $result);
    }

    public function testEmitsFalse(): void
    {
        $emitter = new BoolEmitter();
        $node = new BoolNode($this->makeToken('T_BOOL', 'false'), false);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('false', $result);
    }

    public function testSupportsBoolNode(): void
    {
        $emitter = new BoolEmitter();
        $node = new BoolNode($this->makeToken(), true);

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
