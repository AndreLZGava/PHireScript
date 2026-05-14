<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\TryNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\TryScopeNode;

class TryEmitterTest extends EmitterTestCase
{
    public function testEmitsEmptyTryBlock(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'try');
        $tryScope = new TryScopeNode($token, []);
        $node = new TryNode($token, $tryScope, [], null);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("try\n{\n}\n", $result);
    }

    public function testSupportsTryNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'try');
        $tryScope = new TryScopeNode($token, []);
        $node = new TryNode($token, $tryScope, [], null);

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\TryEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }

    public function testEmitsTryWithBodyStatements(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'try');
        $retToken = $this->makeToken('T_KEYWORD', 'return');
        $retValToken = $this->makeToken('T_NUMBER', '1');

        $returnNode = new \PHireScript\Compiler\Parser\Ast\Nodes\Statements\ReturnNode(
            $retToken,
            new \PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NumberNode($retValToken, 1)
        );

        $tryScope = new TryScopeNode($token, [$returnNode]);
        $node = new TryNode($token, $tryScope, [], null);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("try\n{\nreturn 1;\n}\n", $result);
    }
}
