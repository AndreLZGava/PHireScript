<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\OOP;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Scopes\MethodScopeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamsListNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ReturnTypeNode;
use PHireScript\Compiler\Emitter\OOP\MethodEmitter;

class MethodEmitterTest extends EmitterTestCase
{
    private function makeMethod(string $name, array $modifiers = ['public']): MethodDeclarationNode
    {
        $token = $this->makeToken('T_IDENTIFIER', $name);

        $params = new ParamsListNode($token, []);
        $returnType = new ReturnTypeNode($token, ['void']);
        $body = new MethodScopeNode($token, []);

        $node = new MethodDeclarationNode(
            $token,
            $name,
            $body,
            $modifiers,
            $params,
            $returnType,
            false,
            false,
            null, // not a magic method
        );

        return $node;
    }

    public function testEmitsPublicMethod(): void
    {
        $node = $this->makeMethod('doSomething', ['public']);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertStringContainsString('public function doSomething()', $result);
        $this->assertStringContainsString(': void', $result);
        $this->assertStringContainsString("{\n", $result);
        $this->assertStringContainsString("}\n", $result);
    }

    public function testEmitsPrivateMethod(): void
    {
        $node = $this->makeMethod('helperMethod', ['private']);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertStringContainsString('private function helperMethod()', $result);
    }

    public function testEmitsMethodWithIndent(): void
    {
        $node = $this->makeMethod('execute', ['public']);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        // MethodEmitter always indents with 4 spaces
        $this->assertStringStartsWith('    ', $result);
    }

    public function testSupportsMethodDeclarationNode(): void
    {
        $node = $this->makeMethod('test', ['public']);

        $ctx = $this->makeCtx();
        $emitter = new MethodEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }

    public function testDoesNotSupportOtherNodes(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'pkg');
        $node = new \PHireScript\Compiler\Parser\Ast\Nodes\Declarations\PackageNode($token, 'file.phs', 'App', 'Foo');
        $node->namespace = 'App\\Foo';

        $ctx = $this->makeCtx();
        $emitter = new MethodEmitter();
        $this->assertFalse($emitter->supports($node, $ctx));
    }
}
