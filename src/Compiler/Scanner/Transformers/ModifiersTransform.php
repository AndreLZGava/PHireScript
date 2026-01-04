<?php

namespace PHPScript\Compiler\Scanner\Transformers;
class ModifiersTransform{
    public static function map(string $accessor) {
      $map = [
        '>' => 'set',
        '<' => 'get',
        '+' => 'protected',
        '#' => 'private',
        'readonly' => 'readonly',
        'static' => 'static',
        'async' => 'async'
      ];
      return $map[$accessor];
    }
}
