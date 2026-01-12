<?php

namespace PHPScript\Compiler;

use Exception;

class Validator
{
    private array $forbidden = [
    'namespace' => 'Use "pkg" to declare a package',

    ';' => 'Use break line instead!',
    '::' => 'Use ":" line instead!',
    '->' => 'Use "." line instead!',
    'void' => 'Use "Void" instead!',
    'null' => 'Use "Null" instead!',
    'true' => 'Use "True" instead!',
    'false' => 'Use "False" instead!',
    'string' => 'Use "String" instead!',
    'int' => 'Use "Int" instead!',
    'float' => 'Use "Float" instead!',
    'stdClass' => 'Use "{}" instead!',
    'array' => 'Use "Array" instead!',
    'bool' => 'Use "Bool" instead!',

    'array_key_exists' => 'Use "hasKey" method, allowed to any array variable!',

    'die' => 'Use "Exit" instead!',
    'eval',
    'var_dump' => 'Use "Debug.show(...args)" instead!',

    'public' => 'Use "*" or just leave without definition instead, ' .
      'all methods and classes are public by default!',
    'protected' => 'Use "+" to define a method, class or property as protected!',
    'private' => 'Use "#" to define a method, class or property as private!',

    'function' => 'Declare a function or a method using "arrow function"!',
    '__construct' => 'Declare a constructor using "constructor"!',

    ];

    public function validate(array $tokens): void
    {
        foreach ($tokens as $token) {
            $tokenValue = $token['value'];
            if ($this->isForbidden($tokenValue)) {
                $message = $this->getMessage($tokenValue);
                $line = $token['line'];
                throw new \Exception(
                    "Error: '{$tokenValue}' is not allowed in line {$line}. " . $message
                );
            }
        }
    }

    private function getMessage(string $word): string
    {
        return isset($this->forbidden[$word]) ? $this->forbidden[$word] : '';
    }

    private function isForbidden(string $word): bool
    {
        return array_key_exists($word, $this->forbidden) ||
        in_array($word, $this->forbidden);
    }
}
