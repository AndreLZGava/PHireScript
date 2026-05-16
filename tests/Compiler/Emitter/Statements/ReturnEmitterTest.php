<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ReturnNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NumberNode;

class ReturnEmitterTest extends EmitterTestCase
{
    public function testReturnWithExpression(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'return');
        $numToken = $this->makeToken('T_NUMBER', '42');
        $expr = new NumberNode($numToken, 42);
        $node = new ReturnNode($token, $expr);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('return 42;', $result);
    }

    public function testReturnWithoutExpression(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'return');
        $node = new ReturnNode($token, null);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('return ;', $result);
    }

    public function testSupportsReturnNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'return');
        $node = new ReturnNode($token);

        $ctx = $this->makeCtx();

        $emitter = new \PHireScript\Compiler\Emitter\Statements\ReturnEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }

    public function testDoesNotSupportOtherNodes(): void
    {
        $token = $this->makeToken('T_NUMBER', '1');
        $node = new NumberNode($token, 1);

        $ctx = $this->makeCtx();

        $emitter = new \PHireScript\Compiler\Emitter\Statements\ReturnEmitter();
        $this->assertFalse($emitter->supports($node, $ctx));
    }
}
