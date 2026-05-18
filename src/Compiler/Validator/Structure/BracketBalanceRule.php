<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Validator\Structure;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Validator as CompilerValidator;
use PHireScript\Compiler\Validator\ValidatorRule;
use PHireScript\Runtime\Exceptions\CompileException;

class BracketBalanceRule implements ValidatorRule
{
    /** @var array<string, int> */
    private array $open = ['(' => 0, '{' => 0, '[' => 0, '<' => 0];

    /** @var array<string, int> */
    private array $close = [')' => 0, '}' => 0, ']' => 0, '>' => 0];

    private int $parenDepth = 0;

    public function handleToken(Token $token, CompilerValidator $validator): void
    {
        $value = (string) $token->value;

        $this->count($value, '(', ')');
        $this->count($value, '{', '}');
        $this->count($value, '[', ']');

        if ($value === '(') {
            $this->parenDepth++;
        } elseif ($value === ')') {
            $this->parenDepth--;
        }

        if ($this->parenDepth === 0) {
            $this->count($value, '<', '>');
        }
    }

    public function afterTokens(CompilerValidator $validator): void
    {
        $this->assertBalanced('(', ')');
        $this->assertBalanced('{', '}');
        $this->assertBalanced('[', ']');
        $this->assertBalanced('<', '>');
    }

    private function count(string $value, string $open, string $close): void
    {
        if ($value === $open) {
            $this->open[$open]++;
        }

        if ($value === $close) {
            $this->close[$close]++;
        }
    }

    private function assertBalanced(string $open, string $close): void
    {
        if ($this->open[$open] !== $this->close[$close]) {
            throw new CompileException(
                "Amount of {$open} ({$this->open[$open]}) " .
                    "diverge from {$close} ({$this->close[$close]})",
                0,
                0
            );
        }
    }
}
