<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class CompilerPass
{
    public function __construct(public readonly int $order)
    {
    }
}
