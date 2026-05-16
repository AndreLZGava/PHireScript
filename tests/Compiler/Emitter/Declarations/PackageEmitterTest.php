<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Declarations;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\PackageNode;

class PackageEmitterTest extends EmitterTestCase
{
    public function testEmitsNamespace(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'pkg');
        $node = new PackageNode($token, 'path/to/file.ps', 'App', 'Foo');
        $node->namespace = 'App\\Foo';

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("namespace App\\Foo;\n\n", $result);
    }

    public function testEmitsNamespaceWithDeepPath(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'pkg');
        $node = new PackageNode($token, 'path/to/deep/file.ps', 'MyApp', 'Services');
        $node->namespace = 'MyApp\\Services\\Auth';

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("namespace MyApp\\Services\\Auth;\n\n", $result);
    }

    public function testSupportsPackageNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'pkg');
        $node = new PackageNode($token, 'file.ps', 'App', 'Foo');
        $node->namespace = 'App\\Foo';

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Declarations\PackageEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
