<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Internal;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;

class ConstructorEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ClassDefinition;
    }

    public function emit(object $class, EmitContext $ctx): string
    {
        $props = array_filter(
            $class->body,
            fn ($m) => $m instanceof PropertyDefinition
        );

        if (!$props) {
            return '';
        }

        $params = [];
        $assignments = [];

        foreach ($props as $prop) {
            $type = $ctx->types->phpType($prop);
            $params[] = "{$type} \${$prop->name},";
            $assignments[] = $ctx->types->assignment(
                $prop,
                $ctx->uses
            );
        }

        return <<<PHP

    public function __construct(
        {$this->join($params)}
    ) {
        {$this->join($assignments)}
    }

PHP;
    }
    private function join($params)
    {
        return implode("\n        ", $params);
    }
}
