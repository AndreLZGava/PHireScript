<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use Exception;
use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ReturnTypeNode;
use PHireScript\Helper\Debug\Debug;

class ReturnTypeEmitter implements NodeEmitter
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
            $types[] = mb_strtolower($type);
        }

        $code .= implode('|', $types);

        return $code;
    }
}
