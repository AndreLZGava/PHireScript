<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Signatures;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamArgumentNode;
use PHireScript\Helper\TypeResolver;

class ParamArgumentEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ParamArgumentNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $resolved = \array_map(
            static fn(string $t) => TypeResolver::isPrimitive($t) ? TypeResolver::nativeType($t) : $t,
            $node->types
        );
        $code = \implode('|', $resolved);
        $variable = " $" . $node->name;
        $value = $node->value ? ' = ' . $ctx->emitter->emit($node->value, $ctx) : '';
        $code .= $variable . $value;
        return $code;
    }
}
