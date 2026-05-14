<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Signatures;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamArgumentNode;
use PHireScript\Helper\Debug\Debug;

class ParamArgumentEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ParamArgumentNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = \implode('|', $node->types);
        $variable = " $" . $node->name;
        $value = $node->value ? ' = ' . $ctx->emitter->emit($node->value, $ctx) : '';
        $code .= $variable . $value;
        return $code;
    }
}
