<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Emitter;

final class UseRegistry
{
    private array $uses = [];

    public function add(string $fqcn): void
    {
        $this->uses[$fqcn] = true;
    }

    public function render(): string
    {
        ksort($this->uses);
        return implode("\n", array_map(
            fn ($u) => "use $u;",
            array_keys($this->uses)
        )) . "\n\n";
    }
}
