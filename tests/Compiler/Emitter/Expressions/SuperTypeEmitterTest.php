<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\SuperTypeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\SuperTypeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class SuperTypeEmitterTest extends EmitterTestCase
{
    public function testEmitsSuperTypeCastWithVariableValue(): void
    {
        $emitter = new SuperTypeEmitter();
        $typeToken = new Token('T_SUPER_TYPE', 'Email', 1, 1);
        $varToken = new Token('T_VARIABLE', 'x', 1, 1);
        $varNode = new VariableNode($varToken, 'x');
        $node = new SuperTypeNode($typeToken, $varNode);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('Email::cast($x)', $result);
    }

    public function testAddsUseToRegistry(): void
    {
        $emitter = new SuperTypeEmitter();
        $typeToken = new Token('T_SUPER_TYPE', 'Email', 1, 1);
        $varToken = new Token('T_VARIABLE', 'x', 1, 1);
        $varNode = new VariableNode($varToken, 'x');
        $node = new SuperTypeNode($typeToken, $varNode);
        $ctx = $this->makeCtx();

        $emitter->emit($node, $ctx);

        $uses = $ctx->uses->getUses();
        $this->assertArrayHasKey('PHireScript\Runtime\Types\SuperTypes\Email', $uses);
    }

    public function testEmitsWithNullValue(): void
    {
        $emitter = new SuperTypeEmitter();
        $typeToken = new Token('T_SUPER_TYPE', 'Uuid', 1, 1);
        $node = new SuperTypeNode($typeToken, null);

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('Uuid::cast()', $result);
    }

    public function testSupportsSuperTypeNode(): void
    {
        $emitter = new SuperTypeEmitter();
        $node = new SuperTypeNode(new Token('T_SUPER_TYPE', 'Email', 1, 1));

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
