<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Expressions;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Emitter\Expressions\PropertyAccessEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableNode;

class PropertyAccessEmitterTest extends EmitterTestCase
{
    public function testEmitsPropertyAccess(): void
    {
        $emitter = new PropertyAccessEmitter();
        $objToken = $this->makeToken('T_VARIABLE', 'obj');
        $objNode = new VariableNode($objToken, 'obj');
        $node = new PropertyAccessNode($this->makeToken(), $objNode, 'name');

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('$obj->name', $result);
    }

    public function testEmitsNestedPropertyAccess(): void
    {
        $emitter = new PropertyAccessEmitter();
        $objToken = $this->makeToken('T_VARIABLE', 'user');
        $objNode = new VariableNode($objToken, 'user');
        $node = new PropertyAccessNode($this->makeToken(), $objNode, 'email');

        $result = $emitter->emit($node, $this->makeCtx());

        $this->assertSame('$user->email', $result);
    }

    public function testSupportsPropertyAccessNode(): void
    {
        $emitter = new PropertyAccessEmitter();
        $objNode = new VariableNode($this->makeToken(), 'obj');
        $node = new PropertyAccessNode($this->makeToken(), $objNode, 'prop');

        $this->assertTrue($emitter->supports($node, $this->makeCtx()));
    }
}
