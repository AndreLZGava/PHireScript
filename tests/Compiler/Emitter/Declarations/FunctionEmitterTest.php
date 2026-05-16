<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Declarations;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Emitter\Declarations\FunctionEmitter;

class FunctionEmitterTest extends EmitterTestCase
{
    /**
     * FunctionEmitter is complex (handles native function conversions like strlen,
     * array_map) and requires a fully wired FunctionNode with variableBase, method,
     * params, etc. This test verifies that supports() correctly identifies
     * FunctionNode instances without triggering the full emit pipeline.
     */
    public function testSupportsFunctionNode(): void
    {
        $token = $this->makeToken('T_IDENTIFIER', 'strlen');
        $node = new FunctionNode($token);

        $ctx = $this->makeCtx();
        $emitter = new FunctionEmitter();

        $this->assertTrue($emitter->supports($node, $ctx));
    }

    public function testDoesNotSupportNonFunctionNodes(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'pkg');
        $node = new \PHireScript\Compiler\Parser\Ast\Nodes\Declarations\PackageNode($token, 'file.ps', 'App', 'Foo');
        $node->namespace = 'App\\Foo';

        $ctx = $this->makeCtx();
        $emitter = new FunctionEmitter();

        $this->assertFalse($emitter->supports($node, $ctx));
    }
}
