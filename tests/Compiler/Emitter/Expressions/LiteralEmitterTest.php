<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\LiteralEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;

class LiteralEmitterTest extends EmitterTestCase
{
    public function testEmitsStringRawType(): void
    {
        $emitter = new LiteralEmitter();
        $node = new LiteralNode($this->makeToken(), '"hello"', 'String');

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('"hello"', $result);
    }

    public function testEmitsPropertyRawTypeNormalizesQuotes(): void
    {
        $emitter = new LiteralEmitter();
        $node = new LiteralNode($this->makeToken(), '"test"', 'Property');

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('"test"', $result);
    }

    public function testEmitsPropertyRawTypeConvertsSingleToDouble(): void
    {
        $emitter = new LiteralEmitter();
        $node = new LiteralNode($this->makeToken(), "'test'", 'Property');

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('"test"', $result);
    }

    public function testEmitsDefaultRawTypeCastsToString(): void
    {
        $emitter = new LiteralEmitter();
        $node = new LiteralNode($this->makeToken(), 42, 'Int');

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('42', $result);
    }

    public function testSupportsLiteralNode(): void
    {
        $emitter = new LiteralEmitter();
        $node = new LiteralNode($this->makeToken(), 'test', 'String');

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
