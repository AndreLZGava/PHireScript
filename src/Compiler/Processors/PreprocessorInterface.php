<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Processors;

interface PreprocessorInterface
{
    public function process(string $code): string;
}
