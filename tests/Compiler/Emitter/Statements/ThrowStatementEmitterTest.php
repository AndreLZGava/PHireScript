<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ThrowStatementNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NewExceptionNode;

class ThrowStatementEmitterTest extends EmitterTestCase
{
    public function testEmitsThrowStatement(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'throw');
        $exToken = $this->makeToken('T_KEYWORD', 'new');
        $exceptionExpr = new NewExceptionNode($exToken, 'Exception', 'Something went wrong');
        $node = new ThrowStatementNode($token, $exceptionExpr);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('throw new \Exception("Something went wrong");', $result);
    }

    public function testEmitsThrowWithRuntimeException(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'throw');
        $exToken = $this->makeToken('T_KEYWORD', 'new');
        $exceptionExpr = new NewExceptionNode($exToken, 'RuntimeException', 'Runtime error');
        $node = new ThrowStatementNode($token, $exceptionExpr);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('throw new \RuntimeException("Runtime error");', $result);
    }

    public function testSupportsThrowStatementNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'throw');
        $exToken = $this->makeToken('T_KEYWORD', 'new');
        $exceptionExpr = new NewExceptionNode($exToken, 'Exception', 'msg');
        $node = new ThrowStatementNode($token, $exceptionExpr);

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\ThrowStatementEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
