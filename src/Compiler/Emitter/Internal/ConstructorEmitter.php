<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter\Internal;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;

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
                $prop
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
