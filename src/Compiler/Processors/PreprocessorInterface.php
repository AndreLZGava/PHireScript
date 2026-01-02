<?php

namespace PHPScript\Compiler\Processors;

interface PreprocessorInterface {
    public function process(string $code): string;
}
