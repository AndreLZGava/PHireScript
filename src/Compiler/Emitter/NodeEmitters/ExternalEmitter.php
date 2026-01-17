<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ExternalsStatement;

class ExternalEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ExternalsStatement;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = '';
        foreach ($node->namespaces as $namespace) {
            $code .= "use {$namespace->namespace}";
            if ($namespace->alias) {
                $code .= " as {$namespace->alias}";
            }
            $code .= ";\n";
        }
        return $code;
    }
}
