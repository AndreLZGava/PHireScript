<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Validator\Structure;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Validator as CompilerValidator;
use PHireScript\Compiler\Validator\ValidatorRule;
use PHireScript\Runtime\Exceptions\CompileException;
use PHireScript\Runtime\RuntimeClass;

class ObjectCountRule implements ValidatorRule
{
    private int $count = 0;
    private int $parenDepth = 0;
    private int $curlyDepth = 0;

    public function handleToken(Token $token, CompilerValidator $validator): void
    {
        if ($token->isOpeningParenthesis()) {
            $this->parenDepth++;
            return;
        }

        if ($token->isClosingParenthesis()) {
            $this->parenDepth--;
            return;
        }

        if ($token->isOpeningCurlyBracket()) {
            $this->curlyDepth++;
            return;
        }

        if ($token->isClosingCurlyBracket()) {
            $this->curlyDepth--;
            return;
        }

        if (!\in_array($token->value, RuntimeClass::OBJECT_AS_CLASS, true)) {
            return;
        }

        // Inside parentheses or inside a body: the keyword is not a top-level declaration
        if ($this->parenDepth > 0 || $this->curlyDepth > 0) {
            return;
        }

        $validator->mustHavePkg = true;
        $this->count++;

        if ($this->count > 1) {
            throw new CompileException(
                'Its allowed only one definition of ' .
                    \implode(', ', RuntimeClass::OBJECT_AS_CLASS) . ' per file. Please move ' .
                    'content from line ' . $token->line . ' to another file!',
                $token->line,
                $token->column
            );
        }
    }

    public function afterTokens(CompilerValidator $validator): void
    {
    }
}
