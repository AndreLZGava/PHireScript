<?php

declare(strict_types=1);

namespace PHireScript;

interface TranspilerInterface
{
    public function compile(string $code, string $path): string;
}
