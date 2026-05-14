<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Declarations;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\InterfaceNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\InterfaceBodyNode;

class InterfaceEmitterTest extends EmitterTestCase
{
    public function testEmitsBasicInterface(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'interface');
        $node = new InterfaceNode($token);
        $node->name = 'MyInterface';
        $node->body = new InterfaceBodyNode($token, 'MyInterface', []);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertStringStartsWith('interface MyInterface', $result);
        $this->assertStringContainsString("{\n", $result);
        $this->assertStringContainsString("}\n", $result);
    }

    public function testEmitsInterfaceWithoutExtends(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'interface');
        $node = new InterfaceNode($token);
        $node->name = 'Countable';
        $node->extends = null;
        $node->body = new InterfaceBodyNode($token, 'Countable', []);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("interface Countable\n{\n}\n", $result);
    }

    public function testSetsInsideInterfaceFlagDuringEmit(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'interface');
        $node = new InterfaceNode($token);
        $node->name = 'FlagTest';
        $node->body = new InterfaceBodyNode($token, 'FlagTest', []);

        $ctx = $this->makeCtx();

        // Before emit, insideInterface should be false
        $this->assertFalse($ctx->insideInterface);

        $ctx->emitter->emit($node, $ctx);

        // After emit, insideInterface should be restored to false
        $this->assertFalse($ctx->insideInterface);
    }

    public function testSupportsInterfaceNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'interface');
        $node = new InterfaceNode($token);

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Declarations\InterfaceEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
