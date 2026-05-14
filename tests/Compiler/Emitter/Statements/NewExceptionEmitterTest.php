<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NewExceptionNode;

class NewExceptionEmitterTest extends EmitterTestCase
{
    public function testEmitsNewException(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'new');
        $node = new NewExceptionNode($token, 'Exception', 'Something went wrong');

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('new \Exception("Something went wrong")', $result);
    }

    public function testEmitsNewExceptionWithLeadingBackslash(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'new');
        $node = new NewExceptionNode($token, '\RuntimeException', 'Runtime error');

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('new \RuntimeException("Runtime error")', $result);
    }

    public function testEmitsNewExceptionWithSpecialCharsInMessage(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'new');
        $node = new NewExceptionNode($token, 'InvalidArgumentException', 'Has "quotes" in it');

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        // addslashes escapes double quotes
        $this->assertSame('new \InvalidArgumentException("Has \"quotes\" in it")', $result);
    }

    public function testSupportsNewExceptionNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'new');
        $node = new NewExceptionNode($token, 'Exception', 'msg');

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\NewExceptionEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
