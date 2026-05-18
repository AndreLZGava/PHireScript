<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Validator\Tokens;

use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Validator as CompilerValidator;
use PHireScript\Compiler\Validator\ValidatorRule;
use PHireScript\Runtime\Exceptions\CompileException;

class ForbiddenTokenRule implements ValidatorRule
{
    /** @var array<string, string> */
    private array $forbidden = [
        'namespace' => 'Use "pkg" to declare a package',

        ';' => 'Use break line instead!',
        '::' => 'Use ":" line instead!',
        '->' => 'Use "." line instead!',

        '><' => 'Split like "< >" and follow the order!',
        '#<' => 'Split like "# <" and follow the order!',
        '*<' => 'Split like "* <" and follow the order!',
        '+<' => 'Split like "+ <" and follow the order!',
        '<>' => 'Split like "< >" and follow the order!',
        '#>' => 'Split like "# >" and follow the order!',
        '*>' => 'Split like "* >" and follow the order!',
        '+>' => 'Split like "+ >" and follow the order!',

        'void' => 'Use "Void" instead!',
        'string' => 'Use "String" instead!',
        'int' => 'Use "Int" instead!',
        'float' => 'Use "Float" instead!',
        'stdClass' => 'Use "{}" instead!',
        'array' => 'Use "Array" instead!',
        'bool' => 'Use "Bool" instead!',

        'array_key_exists' => 'Use "hasKey" method, allowed to any array variable!',

        'die' => 'Use "Exit" instead!',
        'eval' => 'Use of eval not permitted!',
        'var_dump' => 'Use "Debug.show(...args)" instead!',

        'public' => 'Use "*" or just leave without definition instead, ' .
            'all methods and classes are public by default!',
        'protected' => 'Use "+" to define a method, class or property as protected!',
        'private' => 'Use "#" to define a method, class or property as private!',

        'function' => 'Declare a function or a method using "arrow function"!',
        '__construct' => 'Declare a constructor using "constructor"!',
    ];

    public function handleToken(Token $token, CompilerValidator $validator): void
    {
        $value = (string) $token->value;

        if (!\array_key_exists($value, $this->forbidden)) {
            return;
        }

        throw new CompileException(
            "Error: '{$value}' is not allowed in line {$token->line}. " . $this->forbidden[$value],
            $token->line,
            $token->column
        );
    }

    public function afterTokens(CompilerValidator $validator): void
    {
    }
}
