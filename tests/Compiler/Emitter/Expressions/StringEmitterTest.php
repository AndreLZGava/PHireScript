<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\StringEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\StringNode;

class StringEmitterTest extends EmitterTestCase
{
    public function testEmitsRawStringValueIncludingQuotes(): void
    {
        $emitter = new StringEmitter();
        $node = new StringNode($this->makeToken('T_STRING_LIT', '"hello"'), '"hello"');

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('"hello"', $result);
    }

    public function testEmitsSingleQuotedString(): void
    {
        $emitter = new StringEmitter();
        $node = new StringNode($this->makeToken('T_STRING_LIT', "'world'"), "'world'");

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame("'world'", $result);
    }

    public function testSupportsStringNode(): void
    {
        $emitter = new StringEmitter();
        $node = new StringNode($this->makeToken(), '"test"');

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
