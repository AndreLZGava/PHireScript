<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\DependenciesStatement;

class DependencyEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof DependenciesStatement;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = '';
        $namespaces = $ctx->dependencyManager->getNodes();
        foreach ($node->packages as $package) {
            $code .= "use {$namespaces[$package->package]->namespace}";
            if ($package->alias) {
                $code .= " as {$package->alias}";
            }
            $code .= ";\n";
        }
        return $code;
    }
}
