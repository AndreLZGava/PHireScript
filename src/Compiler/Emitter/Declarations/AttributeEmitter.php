<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\AttributeNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Helper\TypeResolver;

class AttributeEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof AttributeNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        assert($node instanceof AttributeNode);

        $members = $node->body !== null ? $node->body->children : [];

        $code = "#[\\Attribute]\nclass {$node->name}\n{\n";

        $params = [];
        foreach ($members as $member) {
            if ($member instanceof PropertyNode) {
                $phpTypes = array_map(
                    fn (string $t) => TypeResolver::isPrimitive($t) ? TypeResolver::nativeType($t) : $t,
                    $member->types
                );
                $type = implode('|', $phpTypes);
                $params[] = "public {$type} \${$member->name}";
            }
        }

        if (!empty($params)) {
            $code .= "    public function __construct(\n";
            foreach ($params as $i => $param) {
                $comma = $i < count($params) - 1 ? ',' : '';
                $code .= "        {$param}{$comma}\n";
            }
            $code .= "    ) {}\n";
        }

        return $code . "}\n";
    }
}
