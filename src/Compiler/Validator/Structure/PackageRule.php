<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Validator\Structure;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Validator as CompilerValidator;
use PHireScript\Compiler\Validator\ValidatorRule;
use PHireScript\Runtime\Exceptions\CompileException;
use PHireScript\Runtime\RuntimeClass;

class PackageRule implements ValidatorRule
{
    private bool $hasPkg = false;

    public function handleToken(Token $token, CompilerValidator $validator): void
    {
        if ($token->value !== RuntimeClass::KEYWORD_PACKAGE) {
            return;
        }

        if ($this->hasPkg) {
            throw new CompileException(
                'You must define pkg only once per file!',
                $token->line,
                $token->column
            );
        }

        $this->hasPkg = true;
    }

    public function afterTokens(CompilerValidator $validator): void
    {
        if ($validator->mustHavePkg && !$this->hasPkg) {
            throw new CompileException(
                'You must define a pkg or package for file that contains '
                    . \implode(', ', RuntimeClass::OBJECT_AS_CLASS),
                0,
                0
            );
        }
    }
}
