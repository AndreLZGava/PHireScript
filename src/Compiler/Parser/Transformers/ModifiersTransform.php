<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\Transformers;

use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Helper\Debug\Debug;
use PHPScript\Runtime\RuntimeClass;

class ModifiersTransform
{
    public function __construct(private readonly TokenManager $tokenManager)
    {
    }

    public function map(array $accessor)
    {
        $modifier = in_array($accessor['value'], RuntimeClass::GETTER_AND_SETTER, true) ||
            in_array($accessor['type'], ['T_EOL', 'T_COMMENT'], true)
            ? '*' : $accessor['value'];

        $map = [
            '+' => 'protected',
            '#' => 'private',
            '*' => 'public',
            'abstract' => 'abstract',
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
