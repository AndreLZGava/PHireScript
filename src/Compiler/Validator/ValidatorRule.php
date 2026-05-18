<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Validator;

use PHireScript\Compiler\Validator as CompilerValidator;
use PHireScript\Compiler\Parser\Managers\Token\Token;

interface ValidatorRule
{
    public function handleToken(Token $token, CompilerValidator $validator): void;

    public function afterTokens(CompilerValidator $validator): void;
}
