<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Program;

class ProgramEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof Program;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        $code = "<?php\n\n";

        foreach ($node->statements as $stmt) {
            $code .= $ctx->emitter->emit($stmt, $ctx);
        }

        return $code;
    }
}
