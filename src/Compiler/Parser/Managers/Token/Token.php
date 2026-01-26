<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers\Token;

class Token
{
    public function __construct(
        public readonly string $type,
        public readonly mixed $value,
        public readonly int $line,
        public readonly int $column,
    ) {
    }

    public function isComment()
    {
        return $this->type === 'T_COMMENT';
    }

    public function isStringLiteral()
    {
        return $this->type === 'T_STRING_LIT';
    }

    public function isNumber()
    {
        return $this->type === 'T_NUMBER';
    }

    public function isKeyword()
    {
        return $this->type === 'T_KEYWORD';
    }

    public function isBool()
    {
        return $this->type === 'T_BOOL';
    }
    public function isEndOfLine()
    {
        return $this->type === 'T_EOL';
    }
    public function isWhiteSpace()
    {
        return $this->type === 'T_WHITESPACE';
    }
    public function isAccessor()
    {
        return $this->type === 'T_ACCESSORS';
    }
    public function isModifier()
    {
        return $this->type === 'T_MODIFIER';
    }
    public function isType()
    {
        return $this->type === 'T_TYPE';
    }
    public function isVariable()
    {
        return $this->type === 'T_VARIABLE';
    }
    public function isIdentifier()
    {
        return $this->type === 'T_IDENTIFIER';
    }
    public function isSymbol()
    {
        return $this->type === 'T_SYMBOL';
    }
    public function isBackslash()
    {
        return $this->type === 'T_BACKSLASH';
    }
}
