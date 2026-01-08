<?php

namespace PHPScript\Compiler\Parser\Transformers;

use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Helper\Debug\Debug;

class ModifiersTransform
{
    public function __construct(private TokenManager $tokenManager)
    {
    }

    public function map(array $accessor)
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

        if (!isset($map[$modifier])) {
            Debug::show($accessor, $this->tokenManager->getAll(), debug_backtrace(2));
            exit;
        }

        return $map[$modifier];
    }
}
