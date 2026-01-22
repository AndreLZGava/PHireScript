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
        $props = array_filter($class->body, fn($m) => $m instanceof PropertyDefinition);

        $params = [];
        $assignments = [];

        if ($class->type !== 'class') {
            foreach ($props as $prop) {
                $type = $ctx->types->phpType($prop);
                $params[] = "{$type} \${$prop->name},";
                $assignments[] = $ctx->types->assignment($prop, $ctx->uses);
            }
        }

        $internalStatements = [];
        if (!empty($class->construct->body)) {
            foreach ($class->construct->body as $stmt) {
                $internalStatements[] = $ctx->emitter->emit($stmt, $ctx);
            }
        }

        if (empty($params) && empty($assignments) && empty($internalStatements)) {
            return '';
        }

        return sprintf(
            "\n    public function __construct(\n        %s\n    ) {\n        %s\n        %s\n    }\n",
            implode("\n        ", $params),
            implode("\n        ", $assignments),
            implode("\n        ", $internalStatements)
        );
    }
    private function join($params)
    {
        return implode("\n        ", $params);
    }
}
