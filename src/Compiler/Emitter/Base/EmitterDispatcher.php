<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Base;

use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

class EmitterDispatcher
{
    /** @var NodeEmitter[] */
    private array $emitters = [];

    /**
     * Lazy fast-path: maps node class-name → emitter that last matched it.
     * Most nodes are context-independent, so this gives O(1) after warm-up.
     * When the cached emitter rejects the node (context changed), we fall back
     * to the linear scan and refresh the entry.
     *
     * @var array<class-string, NodeEmitter>
     */
    private array $fastMap = [];

    public function __construct(iterable $emitters)
    {
        foreach ($emitters as $emitter) {
            $this->emitters[] = $emitter;
        }
    }

    public function emit(object $node, EmitContext $context): string
    {
        $class = $node::class;

        if (isset($this->fastMap[$class])) {
            $cached = $this->fastMap[$class];

            if ($cached->supports($node, $context)) {
                return $cached->emit($node, $context);
            }
        }

        foreach ($this->emitters as $emitter) {
            if ($emitter->supports($node, $context)) {
                $this->fastMap[$class] = $emitter;
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
    }
}
