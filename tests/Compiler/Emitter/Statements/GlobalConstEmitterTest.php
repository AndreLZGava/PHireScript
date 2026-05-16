<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\GlobalConstNode;

class GlobalConstEmitterTest extends EmitterTestCase
{
    public function testEmitsGlobalConst(): void
    {
        $token = $this->makeToken('T_CONST', 'MY_CONST');
        $node = new GlobalConstNode($token);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('MY_CONST', $result);
    }

    public function testEmitsGlobalConstWithDifferentValue(): void
    {
        $token = $this->makeToken('T_CONST', 'PHP_EOL');
        $node = new GlobalConstNode($token);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('PHP_EOL', $result);
    }

    public function testSupportsGlobalConstNode(): void
    {
        $token = $this->makeToken('T_CONST', 'SOME_CONST');
        $node = new GlobalConstNode($token);

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\GlobalConstEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
