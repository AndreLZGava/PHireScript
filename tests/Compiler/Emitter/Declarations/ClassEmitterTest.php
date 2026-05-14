<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Declarations;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassBodyNode;

class ClassEmitterTest extends EmitterTestCase
{
    public function testEmitsBasicClass(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'class');
        $node = new ClassNode($token);
        $node->name = 'MyClass';
        $node->modifiers = [];
        $node->body = new ClassBodyNode($token, 'MyClass', 'class', []);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        // modifiers is empty so implode returns '', plus a trailing space
        $this->assertStringContainsString('class MyClass', $result);
        $this->assertStringContainsString("{\n", $result);
        $this->assertStringContainsString("}\n", $result);
    }

    public function testEmitsClassWithReadOnly(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'class');
        $node = new ClassNode($token);
        $node->name = 'ImmutableClass';
        $node->modifiers = [];
        $node->readOnly = true;
        $node->body = new ClassBodyNode($token, 'ImmutableClass', 'class', []);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertStringStartsWith('readonly ', $result);
        $this->assertStringContainsString('class ImmutableClass', $result);
    }

    public function testSupportsClassNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'class');
        $node = new ClassNode($token);
        $node->name = 'Foo';
        $node->modifiers = [];

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Declarations\ClassEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
