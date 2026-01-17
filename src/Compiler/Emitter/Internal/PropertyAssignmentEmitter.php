<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Internal;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;

class PropertyAssignmentEmitter
{
    public function emit(PropertyDefinition $prop, EmitContext $ctx): string
    {
        $var = $prop->name;
        $types = $prop->resolvedTypeInfo;

        $explicitTypes = explode('|', $ctx->types->phpType($prop));
        $itemsToVerify = in_array('null', $explicitTypes, true)
        ? count($types) - 1
        : count($types);

        // Union type
        if ($itemsToVerify > 1) {
            $ctx->uses->add(\PHireScript\Runtime\Types\UnionType::class);

            $classRefs = [];

            foreach ($types as $t) {
                if (isset($t['class'])) {
                    $ctx->uses->add($t['class']);
                    $short = (new \ReflectionClass($t['class']))->getShortName();
                    $classRefs[] = "{$short}::class";
                }
            }

            $classes = implode(', ', $classRefs);

            return "        \$this->{$var} = UnionType::cast(\${$var}, [{$classes}]);";
        }

        $typeInfo = $types[0];

        return match ($typeInfo['category']) {
            'supertype' =>
            "        \$this->{$var} = {$prop->type}::cast(\${$var});",

            'metatype'  =>
            "        \$this->{$var} = \${$var} instanceof {$prop->type} ? \${$var} : new {$prop->type}(\${$var});",

            default     =>
            "        \$this->{$var} = \${$var};",
        };
    }
}
