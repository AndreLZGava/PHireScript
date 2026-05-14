<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\OOP;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use Exception;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ReturnTypeNode;
use PHireScript\Helper\Debug\Debug;

class ReturnTypeEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ReturnTypeNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = ': ';
        $types = [];
        foreach ($node->types as $type) {
            $types[] = \mb_strtolower($type);
        }

        $code .= \implode('|', $types);

        return $code;
    }
}
