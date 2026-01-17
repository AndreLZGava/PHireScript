<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter;

use PHireScript\Helper\Debug\Debug;

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
        Debug::show($node);
        exit;
        //return "// Unknown node: {$node::class}\n";
    }
}
