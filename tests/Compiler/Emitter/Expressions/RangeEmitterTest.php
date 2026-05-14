<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\RangeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\RangeNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class RangeEmitterTest extends EmitterTestCase
{
    public function testEmitsRange(): void
    {
        $emitter = new RangeEmitter();
        $token = new Token('T_RANGE', '1..10', 1, 1);
        $node = new RangeNode($token);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('...\\range(1, 10)', $result);
    }

    public function testRangeNodeParsesLeftAndRight(): void
    {
        $token = new Token('T_RANGE', '5..20', 1, 1);
        $node = new RangeNode($token);

        $this->assertSame(5, $node->left);
        $this->assertSame(20, $node->right);
    }

    public function testEmitsRangeWithDifferentBounds(): void
    {
        $emitter = new RangeEmitter();
        $token = new Token('T_RANGE', '0..100', 1, 1);
        $node = new RangeNode($token);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('...\\range(0, 100)', $result);
    }

    public function testSupportsRangeNode(): void
    {
        $emitter = new RangeEmitter();
        $node = new RangeNode(new Token('T_RANGE', '1..5', 1, 1));

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
