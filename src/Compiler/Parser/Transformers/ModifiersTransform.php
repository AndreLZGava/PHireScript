<?php

namespace PHPScript\Compiler\Parser\Transformers;

use PHPScript\Helper\Debug\Debug;

class ModifiersTransform
{
    public static function map(array $accessor)
    {
        $modifier = $accessor['type'] === 'T_EOL' ||
        $accessor['type'] === 'T_COMMENT'
        ? '*' : $accessor['value'];

        $map = [
        '>' => 'set',
        '<' => 'get',
        '+' => 'protected',
        '#' => 'private',
        '*' => 'public',
        'readonly' => 'readonly',
        'static' => 'static',
        'async' => 'async'
        ];

        return $map[$modifier] ?? Debug::show(debug_backtrace(2));
    }
}
