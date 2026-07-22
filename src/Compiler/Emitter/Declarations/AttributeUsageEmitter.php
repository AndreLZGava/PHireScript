<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\AttributeUsageNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NamedArgNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;

class AttributeUsageEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    /** PHP built-in attributes that must be emitted as #[\Name] */
    private const PHP_BUILTINS = [
        'Attribute',
        'AllowDynamicProperties',
        'Deprecated',
        'Override',
        'ReturnTypeWillChange',
        'SensitiveParameter',
    ];

    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof AttributeUsageNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        assert($node instanceof AttributeUsageNode);

        $name = \in_array($node->name, self::PHP_BUILTINS, true)
            ? '\\' . $node->name
            : $node->name;

        if ($node->params === null || empty($node->params->params)) {
            return "#[{$name}]\n";
        }

        $args = [];
        foreach ((array) $node->params->params as $param) {
            if ($param instanceof NamedArgNode && $param->value !== null) {
                $value = $ctx->emitter->emit($param->value, $ctx);
                $args[] = "{$param->paramName}: {$value}";
            } elseif ($param instanceof Node) {
                $args[] = $ctx->emitter->emit($param, $ctx);
            }
        }

        $argsStr = implode(', ', $args);
        return "#[{$name}({$argsStr})]\n";
    }
}
