<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\ExternalsStatement;

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
