<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers\Builder;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Helper\Debug\Debug;

class SequenceBuilder
{
    /** @var array<int, array<string, mixed>> */
    private array $rules = [];
    /** @var (callable(Token): bool)|null */
    private $untilCallback = null;
    private int $direction = 1;
    private bool $debug = false;

    // Forward limit is higher because lookahead patterns span more tokens than lookbehind ones.
    // Keeping these low avoids runaway loops when a rule is misconfigured.
    private const MAX_FORWARD = 20;
    private const MAX_BACKWARD = 10;

    public function __construct(
        private readonly TokenManager $tokenManager
    ) {
    }

    public function lookAhead(): self
    {
        $this->direction = 1;
        return $this;
    }

    public function lookBehind(): self
    {
        $this->direction = -1;
        return $this;
    }

    public function once(callable $match): self
    {
        $this->rules[] = ['type' => 'once', 'match' => $match];
        return $this;
    }

    public function then(callable $match): self
    {
        return $this->once($match);
    }

    public function separated(callable $match, callable $separator): self
    {
        $this->rules[] = ['type' => 'separated', 'match' => $match, 'separator' => $separator];
        return $this;
    }

    public function test(callable $builderFn, int $offset = 0): bool
    {
        return $this->spawnSubBuilder($builderFn, $offset)['matched'];
    }

    public function optional(callable $builderFn): self
    {
        $this->rules[] = ['type' => 'optional', 'builder' => $builderFn];
        return $this;
    }

    public function skipUntil(callable $callback): self
    {
        $this->rules[] = ['type' => 'skip_until', 'callback' => $callback];
        return $this;
    }

    public function or(callable ...$builders): self
    {
        $this->rules[] = ['type' => 'or', 'builders' => $builders];
        return $this;
    }

    public function around(callable $backward, callable $forward): self
    {
        $this->rules[] = ['type' => 'around', 'backward' => $backward, 'forward' => $forward];
        return $this;
    }

    public function group(callable $builderFn): self
    {
        $this->rules[] = ['type' => 'group', 'builder' => $builderFn];
        return $this;
    }

    public function until(callable $callback): self
    {
        $this->untilCallback = $callback;
        return $this;
    }

    public function debug(): self
    {
        $this->debug = true;
        return $this;
    }

    public function match(): bool
    {
        return $this->executeSub($this, 0)['matched'];
    }

    // -------------------------------------------------------------------------
    // Execution engine
    // -------------------------------------------------------------------------

    private function executeSub(self $builder, int $startOffset): array
    {
        $offset = $startOffset;
        $steps  = 0;
        $limit  = $builder->direction === 1 ? self::MAX_FORWARD : self::MAX_BACKWARD;

        foreach ($builder->rules as $rule) {
            if ($this->debug) {
                $token = $this->peek($offset);
                $tokenDisplay = \is_scalar($token->value) ? (string) $token->value : 'null';
                $ruleType = \is_scalar($rule['type']) ? (string) $rule['type'] : 'unknown';
                Debug::show("[DEBUG] Rule: {$ruleType} | Token: {$tokenDisplay} | Offset: {$offset}");
            }

            if ($steps >= $limit) {
                return ['matched' => false, 'consumed' => 0, 'finalOffset' => $startOffset];
            }

            $continued = match ($rule['type']) {
                'once'       => $this->executeOnce($rule, $builder, $offset, $steps),
                'separated'  => $this->executeSeparated($rule, $builder, $offset, $steps, $limit),
                'optional'   => $this->executeOptional($rule, $builder, $offset, $steps),
                'group'      => $this->executeGroup($rule, $builder, $offset, $steps),
                'or'         => $this->executeOr($rule, $builder, $offset, $steps),
                'skip_until' => $this->executeSkipUntil($rule, $builder, $offset, $steps, $limit),
                'around'     => $this->executeAround($rule),
                default      => true,
            };

            if (!$continued) {
                return ['matched' => false, 'consumed' => 0, 'finalOffset' => $startOffset];
            }
        }

        return ['matched' => true, 'consumed' => $steps, 'finalOffset' => $offset];
    }

    // -------------------------------------------------------------------------
    // Rule handlers
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $rule */
    private function executeOnce(array $rule, self $builder, int &$offset, int &$steps): bool
    {
        $token = $this->peek($offset);
        /** @var callable(Token): bool $match */
        $match = $rule['match'];

        if ($builder->shouldStop($token) || !$match($token)) {
            return false;
        }

        $offset += $builder->direction;
        $steps++;
        return true;
    }

    /** @param array<string, mixed> $rule */
    private function executeSeparated(array $rule, self $builder, int &$offset, int &$steps, int $limit): bool
    {
        /** @var callable(Token): bool $match */
        $match = $rule['match'];
        /** @var callable(Token): bool $separator */
        $separator = $rule['separator'];
        $matched = false;

        while (true) {
            if ($steps >= $limit) {
                return false;
            }

            $token = $this->peek($offset);

            if ($builder->shouldStop($token) || !$match($token)) {
                break;
            }

            $offset += $builder->direction;
            $steps++;
            $matched = true;

            $separatorToken = $this->peek($offset);

            if ($separator($separatorToken)) {
                $offset += $builder->direction;
                $steps++;
                continue;
            }

            break;
        }

        return $matched;
    }

    /** @param array<string, mixed> $rule */
    private function executeOptional(array $rule, self $builder, int &$offset, int &$steps): bool
    {
        /** @var callable(self): void $builderFn */
        $builderFn = $rule['builder'];
        $sub = $this->executeSub($this->buildSub($builderFn, $builder), $offset);

        if ($sub['matched']) {
            $offset = (int) $sub['finalOffset'];
            $steps += (int) $sub['consumed'];
        }

        return true;
    }

    /** @param array<string, mixed> $rule */
    private function executeGroup(array $rule, self $builder, int &$offset, int &$steps): bool
    {
        /** @var callable(self): void $builderFn */
        $builderFn = $rule['builder'];
        $sub = $this->executeSub($this->buildSub($builderFn, $builder), $offset);

        if (!$sub['matched']) {
            return false;
        }

        $offset = (int) $sub['finalOffset'];
        $steps += (int) $sub['consumed'];
        return true;
    }

    /** @param array<string, mixed> $rule */
    private function executeOr(array $rule, self $builder, int &$offset, int &$steps): bool
    {
        /** @var array<int, callable(self): void> $builders */
        $builders = $rule['builders'];

        foreach ($builders as $builderFn) {
            $sub = $this->executeSub($this->buildSub($builderFn, $builder), $offset);

            if ($sub['matched']) {
                $offset = (int) $sub['finalOffset'];
                $steps += (int) $sub['consumed'];
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $rule */
    private function executeSkipUntil(array $rule, self $builder, int &$offset, int &$steps, int $limit): bool
    {
        /** @var callable(Token): bool $callback */
        $callback = $rule['callback'];

        while (true) {
            if ($steps >= $limit) {
                return false;
            }

            $token = $this->peek($offset);

            if ($callback($token)) {
                break;
            }

            $offset += $builder->direction;
            $steps++;
        }

        return true;
    }

    /** @param array<string, mixed> $rule */
    private function executeAround(array $rule): bool
    {
        /** @var callable(self): void $backward */
        $backward = $rule['backward'];
        /** @var callable(self): void $forward */
        $forward = $rule['forward'];

        $back = $this->spawnDirectionalSub($backward, -1, 0);

        if (!$back['matched']) {
            return false;
        }

        $fwd = $this->spawnDirectionalSub($forward, 1, 0);
        return $fwd['matched'];
    }

    // -------------------------------------------------------------------------
    // Sub-builder factory helpers
    // -------------------------------------------------------------------------

    private function spawnSubBuilder(callable $builderFn, int $startOffset): array
    {
        return $this->executeSub($this->buildSub($builderFn, $this), $startOffset);
    }

    private function spawnDirectionalSub(callable $builderFn, int $direction, int $startOffset): array
    {
        $subBuilder = new self($this->tokenManager);
        $subBuilder->direction = $direction;
        $subBuilder->untilCallback = $this->untilCallback;

        $builderFn($subBuilder);

        return $this->executeSub($subBuilder, $startOffset);
    }

    private function buildSub(callable $builderFn, self $parent): self
    {
        $subBuilder = new self($this->tokenManager);
        $subBuilder->direction = $parent->direction;
        $subBuilder->untilCallback = $parent->untilCallback;

        $builderFn($subBuilder);

        return $subBuilder;
    }

    // -------------------------------------------------------------------------
    // Token access
    // -------------------------------------------------------------------------

    private function peek(int $offset): Token
    {
        return $this->tokenManager->peek($offset);
    }

    private function shouldStop(Token $token): bool
    {
        return $this->untilCallback !== null && ($this->untilCallback)($token);
    }
}
