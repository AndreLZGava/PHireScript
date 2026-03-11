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
        public ?string $processedBy = null,
    ) {
    }

    public function isComment(): bool
    {
        return $this->type === 'T_COMMENT';
    }

    public function isStringLiteral(): bool
    {
        return $this->type === 'T_STRING_LIT';
    }

    public function isNumber(): bool
    {
        return $this->type === 'T_NUMBER';
    }

    public function isKeyword(): bool
    {
        return $this->type === 'T_KEYWORD';
    }

    public function isBool(): bool
    {
        return $this->type === 'T_BOOL';
    }
    public function isEndOfLine(): bool
    {
        return $this->type === 'T_EOL';
    }
    public function isWhiteSpace(): bool
    {
        return $this->type === 'T_WHITESPACE';
    }
    public function isAccessor(): bool
    {
        return $this->type === 'T_ACCESSORS';
    }
    public function isModifier(): bool
    {
        return $this->type === 'T_MODIFIER';
    }
    public function isPrimitive(): bool
    {
        return $this->type === 'T_PRIMITIVE';
    }

    public function isSuperType(): bool
    {
        return $this->type === 'T_SUPER_TYPE';
    }

    public function isMetaType(): bool
    {
        return $this->type === 'T_META_TYPE';
    }

    public function isNull(): bool
    {
        return $this->type === 'T_NULL';
    }

    public function isVariable(): bool
    {
        return $this->type === 'T_VARIABLE';
    }
    public function isIdentifier(): bool
    {
        return $this->type === 'T_IDENTIFIER';
    }
    public function isSymbol(): bool
    {
        return $this->type === 'T_SYMBOL';
    }
    public function isBackslash(): bool
    {
        return $this->type === 'T_BACKSLASH';
    }
}
