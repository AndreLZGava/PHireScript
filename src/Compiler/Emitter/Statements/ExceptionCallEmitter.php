<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Statements;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ExceptionCallNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NamedArgNode;

class ExceptionCallEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    private const CAUSE_REMAP = 'previous';

    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ExceptionCallNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $parts = [];

        foreach ($node->args as $arg) {
            if (!$arg instanceof NamedArgNode) {
                continue;
            }

            $name = $arg->paramName === 'cause' ? self::CAUSE_REMAP : $arg->paramName;
            $value = $arg->value !== null ? $ctx->emitter->emit($arg->value, $ctx) : 'null';

            $parts[] = "{$name}: {$value}";
        }

        $argsStr = \implode(', ', $parts);
        $typeName = \ltrim($node->typeName, '\\');

        return "new {$typeName}({$argsStr})";
    }
}
