<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;

class VariableDeclarationEmitterTest extends EmitterTestCase
{
    public function testEmitsVariableDeclaration(): void
    {
        $token = $this->makeToken('T_VARIABLE', 'myVar');
        $node = new VariableDeclarationNode($token, 'myVar');

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('$myVar', $result);
    }

    public function testEmitsVariableDeclarationWithDifferentName(): void
    {
        $token = $this->makeToken('T_VARIABLE', 'counter');
        $node = new VariableDeclarationNode($token, 'counter');

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('$counter', $result);
    }

    public function testSupportsVariableDeclarationNode(): void
    {
        $token = $this->makeToken('T_VARIABLE', 'x');
        $node = new VariableDeclarationNode($token, 'x');

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\VariableDeclarationEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
