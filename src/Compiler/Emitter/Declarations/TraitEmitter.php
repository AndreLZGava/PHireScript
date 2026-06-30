<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\OOP\GetterSetterEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\TraitNode;

class TraitEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof TraitNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        assert($node instanceof TraitNode);

        $members = $node->body?->children ?? [];

        $code = "trait {$node->name} {\n";

        foreach ($members as $member) {
            if ($member instanceof PropertyNode) {
                $code .= $ctx->emitter->emit($member, $ctx);
            }
        }

        foreach ($members as $member) {
            if ($member instanceof MethodDeclarationNode) {
                $code .= $ctx->emitter->emit($member, $ctx);
            }
        }

        if ($node->body !== null) {
            $code .= (new GetterSetterEmitter())->emit($node->body, $ctx);
        }

        return $code . "}\n";
    }
}
