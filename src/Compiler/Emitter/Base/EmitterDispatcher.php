<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Base;

use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

class EmitterDispatcher
{
    /** @var NodeEmitter[] */
    private array $emitters = [];

    public function __construct(iterable $emitters)
    {
        foreach ($emitters as $emitter) {
            $this->emitters[] = $emitter;
        }
    }

    public function emit(object $node, EmitContext $context): string
    {
        foreach ($this->emitters as $emitter) {
            if ($emitter->supports($node, $context)) {
                return $emitter->emit($node, $context);
            }
        }
        if (empty($node->token)) {
            if (!isset($node->line) && !isset($node->column)) {
                Debug::show($node, $node::class);
                exit;
            }
            throw new CompileException(
                $node::class . ' has no emitter defined to process!',
                $node->line,
                $node->column,
            );
        }
        throw new CompileException(
            $node::class . ' has no emitter defined to process!',
            $node->token->line,
            $node->token->column,
        );
        //return "// Unknown node: {$node::class}\n";
    }
}
