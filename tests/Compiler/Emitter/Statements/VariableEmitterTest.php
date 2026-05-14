<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableNode;

class VariableEmitterTest extends EmitterTestCase
{
    public function testEmitsVariable(): void
    {
        $token = $this->makeToken('T_VARIABLE', 'myVar');
        $node = new VariableNode($token, 'myVar');

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('$myVar', $result);
    }

    public function testEmitsVariableWithCamelCaseName(): void
    {
        $token = $this->makeToken('T_VARIABLE', 'someObject');
        $node = new VariableNode($token, 'someObject');

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('$someObject', $result);
    }

    public function testSupportsVariableNode(): void
    {
        $token = $this->makeToken('T_VARIABLE', 'x');
        $node = new VariableNode($token, 'x');

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\VariableEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
