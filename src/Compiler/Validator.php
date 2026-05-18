<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Validator\Structure\BracketBalanceRule;
use PHireScript\Compiler\Validator\Structure\ObjectCountRule;
use PHireScript\Compiler\Validator\Structure\PackageRule;
use PHireScript\Compiler\Validator\Tokens\ForbiddenTokenRule;
use PHireScript\Compiler\Validator\ValidatorRule;

class Validator
{
    public bool $mustHavePkg = false;

    /** @var ValidatorRule[] */
    public array $rules = [];

    public function __construct()
    {
        $this->rules = [
            new ForbiddenTokenRule(),
            new ObjectCountRule(),
            new BracketBalanceRule(),
            new PackageRule(),
        ];
    }

    /** @param \PHireScript\Compiler\Parser\Managers\Token\Token[] $tokens */
    public function validate(array $tokens): void
    {
        foreach ($tokens as $token) {
            foreach ($this->rules as $rule) {
                $rule->handleToken($token, $this);
            }
        }

        foreach ($this->rules as $rule) {
            $rule->afterTokens($this);
        }
    }
}
