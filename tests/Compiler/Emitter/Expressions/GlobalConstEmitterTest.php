<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Statements\GlobalConstEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\GlobalConstNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class GlobalConstEmitterTest extends EmitterTestCase
{
    public function testEmitsConstantName(): void
    {
        $emitter = new GlobalConstEmitter();
        $token = new Token('T_CONST', 'FOO_BAR', 1, 1);
        $node = new GlobalConstNode($token);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('FOO_BAR', $result);
    }

    public function testEmitsUppercaseConstant(): void
    {
        $emitter = new GlobalConstEmitter();
        $token = new Token('T_CONST', 'PHP_EOL', 1, 1);
        $node = new GlobalConstNode($token);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('PHP_EOL', $result);
    }

    public function testSupportsGlobalConstNode(): void
    {
        $emitter = new GlobalConstEmitter();
        $node = new GlobalConstNode(new Token('T_CONST', 'MY_CONST', 1, 1));

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
