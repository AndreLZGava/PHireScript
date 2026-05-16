<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Expressions;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;
use PHireScript\Helper\Debug\Debug;

class LiteralEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof LiteralNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return match ($node->rawType) {
            'String' => $node->value,
            'Property' => $this->clearAsString($node->value),
            default  => (string) $node->value
        };
    }

    private function clearAsString($found)
    {
        $found = \trim((string) $found, '"');
        $found = \trim($found, "'");
        return '"' . $found . '"';
    }
}
