<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\IfNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ReturnNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\IfConditionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\IfScopeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\ElseScopeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NumberNode;

class IfStatementEmitterTest extends EmitterTestCase
{
    public function testEmitsIfWithoutElse(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'if');
        $numToken = $this->makeToken('T_NUMBER', '1');
        $retToken = $this->makeToken('T_KEYWORD', 'return');
        $retValToken = $this->makeToken('T_NUMBER', '42');

        $condition = new IfConditionNode($token, [new NumberNode($numToken, 1)]);
        $body = new IfScopeNode($token, [new ReturnNode($retToken, new NumberNode($retValToken, 42))]);
        $node = new IfNode($token, $condition, $body);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("if (1) {\n return 42;\n}", $result);
    }

    public function testEmitsIfWithElse(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'if');
        $numToken = $this->makeToken('T_NUMBER', '1');
        $retToken = $this->makeToken('T_KEYWORD', 'return');
        $retValToken = $this->makeToken('T_NUMBER', '42');
        $elseValToken = $this->makeToken('T_NUMBER', '0');

        $condition = new IfConditionNode($token, [new NumberNode($numToken, 1)]);
        $body = new IfScopeNode($token, [new ReturnNode($retToken, new NumberNode($retValToken, 42))]);
        $elseBody = new ElseScopeNode($token, [new ReturnNode($retToken, new NumberNode($elseValToken, 0))]);
        $node = new IfNode($token, $condition, $body, [], $elseBody);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("if (1) {\n return 42;\n} else {\n return 0;\n}", $result);
    }

    public function testEmitsIfWithEmptyBody(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'if');
        $numToken = $this->makeToken('T_NUMBER', '0');

        $condition = new IfConditionNode($token, [new NumberNode($numToken, 0)]);
        $body = new IfScopeNode($token, []);
        $node = new IfNode($token, $condition, $body);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("if (0) {\n \n}", $result);
    }

    public function testSupportsIfNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'if');
        $condition = new IfConditionNode($token, []);
        $body = new IfScopeNode($token, []);
        $node = new IfNode($token, $condition, $body);

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\IfStatementEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
