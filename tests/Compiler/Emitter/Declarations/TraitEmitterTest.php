<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\Declarations;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\TraitNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassBodyNode;

class TraitEmitterTest extends EmitterTestCase
{
    public function testEmitsBasicTrait(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'trait');
        $node = new TraitNode($token);
        $node->name = 'MyTrait';
        $node->body = null;

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("trait MyTrait {\n}\n", $result);
    }

    public function testEmitsTraitWithEmptyBody(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'trait');
        $node = new TraitNode($token);
        $node->name = 'HasTimestamps';
        $node->body = new ClassBodyNode($token, 'HasTimestamps', 'trait', []);

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("trait HasTimestamps {\n}\n", $result);
    }

    public function testSupportsTraitNode(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'trait');
        $node = new TraitNode($token);
        $node->name = 'Foo';

        $ctx = $this->makeCtx();
        $emitter = new \PHireScript\Compiler\Emitter\Declarations\TraitEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }
}
