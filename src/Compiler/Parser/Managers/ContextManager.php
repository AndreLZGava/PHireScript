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
    private array $stack = [];

    public function __construct(private ContextState $current)
    {
    }

    public function getCurrentContextElement(): Node
    {
        return $this->current->element;
    }

    public function getCurrentContext(): string
    {
        return $this->current->context->value;
    }

    public function enterContext(Context $context, Node $element): void
    {
        $this->stack[] = $this->current;
        $this->current = new ContextState($context, $element);
    }

    public function exitContext(): void
    {
        if (!empty($this->stack)) {
            $this->current = array_pop($this->stack);
        }
    }

    public function contextAsClass(Node $classNode): void
    {
        $this->enterContext(Context::ClassType, $classNode);
    }

    public function contextAsMethod(Node $methodNode): void
    {
        $this->enterContext(Context::Method, $methodNode);
    }

    public function definingVariable(Node $variable): void
    {
        $this->enterContext(Context::Variable, $variable);
    }

    public function definingQueue(Node $nodeParam): void
    {
        $this->enterContext(Context::Queue, $nodeParam);
    }

    public function isInContext(Context $context): bool
    {
        if ($this->current?->context === $context) {
            return true;
        }

        foreach ($this->stack as $state) {
            if ($state->context === $context) {
                return true;
            }
        }

        return false;
    }
}
