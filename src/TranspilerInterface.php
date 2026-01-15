<?php

namespace PHPScript;

interface TranspilerInterface
{
    public function compile(string $code, string $path): string;
}
