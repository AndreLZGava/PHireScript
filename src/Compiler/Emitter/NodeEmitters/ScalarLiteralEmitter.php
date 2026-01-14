<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter\NodeEmitters;

use PHPScript\Compiler\Emitter\EmitContext;
use PHPScript\Compiler\Emitter\NodeEmitter;
use PHPScript\Compiler\Parser\Ast\Literal\BoolLiteral;
use PHPScript\Compiler\Parser\Ast\Literal\FloatLiteral;
use PHPScript\Compiler\Parser\Ast\Literal\IntLiteral;
use PHPScript\Compiler\Parser\Ast\Literal\NullLiteral;
use PHPScript\Compiler\Parser\Ast\Literal\StringLiteral;

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
