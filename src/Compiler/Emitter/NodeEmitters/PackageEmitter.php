<?php

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\PackageStatement;

class PackageEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof PackageStatement;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return "namespace {$node->namespace};\n\n";
    }
}
