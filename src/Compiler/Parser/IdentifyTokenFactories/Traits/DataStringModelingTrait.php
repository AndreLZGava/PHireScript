<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits;

trait DataStringModelingTrait
{
    public function clearQuotes(mixed $value)
    {
        if (is_string($value)) {
            $value = trim($value, '"');
            $value = trim($value, "'");
        }
        return $value;
    }
}
