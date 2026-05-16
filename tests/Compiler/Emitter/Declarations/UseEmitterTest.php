<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Declarations;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\UseNode;
use PHireScript\Compiler\Emitter\Statements\VariableNode;

class UseEmitterTest extends EmitterTestCase
{
    /**
     * UseEmitter.supports() returns true for UseNode instances.
     */
    public function testSupportsUseNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'use');
        $node = new UseNode($token, []);

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Declarations\UseEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }

    /**
     * UseEmitter.supports() returns false for non-UseNode instances.
     */
    public function testDoesNotSupportOtherNodes(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'pkg');
        $node = new \PHireScript\Compiler\Parser\Ast\Nodes\Declarations\PackageNode($token, 'file.ps', 'App', 'Foo');
        $node->namespace = 'App\\Foo';

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Declarations\UseEmitter();
        $this->assertFalse($emitter->supports($node, $ctx));
    }

    /**
     * UseEmitter with an empty packages list emits an empty string.
     * This avoids the need for a fully wired DependencyGraphBuilder with registered packages.
     */
    public function testEmitsEmptyStringForEmptyPackageList(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'use');
        $node = new UseNode($token, []);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame('', $result);
    }
}
