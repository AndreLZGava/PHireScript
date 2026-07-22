<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Emitter\OOP;

use PHireScript\Tests\Compiler\Emitter\EmitterTestCase;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Emitter\OOP\PropertyEmitter;

class PropertyEmitterTest extends EmitterTestCase
{
    public function testEmitsPublicStringProperty(): void
    {
        $token = $this->makeToken('T_IDENTIFIER', 'myProp');
        $node = new PropertyNode(
            $token,
            ['String'],
            'myProp',
            null,
            ['public'],
            [['category' => 'primitive', 'native' => 'string', 'name' => 'String']],
        );

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("    public string \$myProp;\n", $result);
    }

    public function testEmitsPrivateIntProperty(): void
    {
        $token = $this->makeToken('T_IDENTIFIER', 'count');
        $node = new PropertyNode(
            $token,
            ['Int'],
            'count',
            null,
            ['private'],
            [['category' => 'primitive', 'native' => 'int', 'name' => 'Int']],
        );

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("    private int \$count;\n", $result);
    }

    public function testEmitsPropertyWithNullableType(): void
    {
        $token = $this->makeToken('T_IDENTIFIER', 'label');
        $node = new PropertyNode(
            $token,
            ['String', 'Null'],
            'label',
            null,
            ['protected'],
            [
                ['category' => 'primitive', 'native' => 'string', 'name' => 'String'],
                ['category' => 'primitive', 'native' => 'null', 'name' => 'Null'],
            ],
        );

        $ctx = $this->makeCtx();
        $result = $ctx->emitter->emit($node, $ctx);

        $this->assertSame("    protected string|null \$label;\n", $result);
    }

    public function testSupportsPropertyNode(): void
    {
        $token = $this->makeToken('T_IDENTIFIER', 'prop');
        $node = new PropertyNode($token, ['String'], 'prop', null, ['public'], []);

        $ctx = $this->makeCtx();
        $emitter = new PropertyEmitter();
        $this->assertTrue($emitter->supports($node, $ctx));
    }

    public function testDoesNotSupportOtherNodes(): void
    {
        $token = $this->makeToken('T_KEYWORD', 'pkg');
        $node = new \PHireScript\Compiler\Parser\Ast\Nodes\Declarations\PackageNode($token, 'file.phs', 'App', 'Foo');
        $node->namespace = 'App\\Foo';

        $ctx = $this->makeCtx();
        $emitter = new PropertyEmitter();
        $this->assertFalse($emitter->supports($node, $ctx));
    }
}
