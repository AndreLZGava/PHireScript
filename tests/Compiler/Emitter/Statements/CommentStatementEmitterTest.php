<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Meta\CommentNode;

class CommentStatementEmitterTest extends EmitterTestCase
{
    public function testEmitsSingleLineComment(): void
    {
        $token = $this->makeToken('T_COMMENT', '// this is a comment');
        $node = new CommentNode($token);
        $node->code = '// this is a comment';

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("    // this is a comment\n", $result);
    }

    public function testCommentAlreadyHasNewline(): void
    {
        $token = $this->makeToken('T_COMMENT', '// already has newline');
        $node = new CommentNode($token);
        $node->code = '// already has newline' . "\n";

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        // rtrim strips trailing whitespace/newline, then newline is added back
        $this->assertSame("    // already has newline\n", $result);
    }

    public function testSupportsCommentNode(): void
    {
        $token = $this->makeToken('T_COMMENT', '// test');
        $node = new CommentNode($token);
        $node->code = '// test';

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\CommentStatementEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
