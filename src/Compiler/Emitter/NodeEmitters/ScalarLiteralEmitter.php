<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\Literal\BoolLiteral;
use PHireScript\Compiler\Parser\Ast\Literal\FloatLiteral;
use PHireScript\Compiler\Parser\Ast\Literal\IntLiteral;
use PHireScript\Compiler\Parser\Ast\Literal\NullLiteral;
use PHireScript\Compiler\Parser\Ast\Literal\StringLiteral;
use PHireScript\Helper\Debug\Debug;

/**
 * Apparently not used.
 */
class ScalarLiteralEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof IntLiteral
            || $node instanceof FloatLiteral
            || $node instanceof StringLiteral
            || $node instanceof BoolLiteral
            || $node instanceof NullLiteral;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        return match (true) {
            $node instanceof StringLiteral => var_export($node->value, true),
            $node instanceof BoolLiteral   => $node->value ? 'true' : 'false',
            $node instanceof NullLiteral   => 'null',
            default                        => (string) $node->value,
        };
    }
}
