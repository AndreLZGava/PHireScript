<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Managers\Context\Context;
use PHireScript\Compiler\Parser\Managers\Context\ContextState;
use PHireScript\Helper\Debug\Debug;

class ContextManager
{
    public array $config;
    public string $path;
    private ContextState $current;

    public function __construct(ContextState $root)
    {
        $this->current = $root;
    }

    public function current(): ContextState
    {
        return $this->current;
    }

    public function enter(Context $context, Node $element): ContextState
    {
        $state = new ContextState($context, $element, $this->current);
        $this->current->addChild($state);

        $this->current = $state;

        return $state;
    }

    public function exitContext(): void
    {
        if ($this->current->parent !== null) {
            $this->current = $this->current->parent;
        }
    }

    public function exitUntil(Context $context): void
    {
        while ($this->current->parent !== null && !$this->current->is($context)) {
            $this->current = $this->current->parent;
        }
    }

    public function isIn(Context $context): bool
    {
        $cursor = $this->current;

        while ($cursor !== null) {
            if ($cursor->is($context)) {
                return true;
            }
            $cursor = $cursor->parent;
        }

        return false;
    }
}
