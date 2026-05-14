<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Statements;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NumberNode;

class AssignmentEmitterTest extends EmitterTestCase
{
    public function testEmitsAssignment(): void
    {
        $token = $this->makeToken('T_SYMBOL', '=');
        $leftToken = $this->makeToken('T_VARIABLE', 'x');
        $rightToken = $this->makeToken('T_NUMBER', '10');

        $left = new VariableNode($leftToken, 'x');
        $right = new NumberNode($rightToken, 10);
        $node = new AssignmentNode($token, $left, $right);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('$x = 10;', $result);
    }

    public function testSupportsAssignmentNode(): void
    {
        $token = $this->makeToken('T_SYMBOL', '=');
        $leftToken = $this->makeToken('T_VARIABLE', 'foo');
        $left = new VariableNode($leftToken, 'foo');
        $node = new AssignmentNode($token, $left);

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Statements\AssignmentEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
