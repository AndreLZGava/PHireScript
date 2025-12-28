<?php

namespace App;

class SymbolTable {
    private array $symbols = [];

    public function set(string $name, string $type) {
        $this->symbols[$name] = $type;
    }

    public function get(string $name): ?string {
        return $this->symbols[$name] ?? null;
    }

    public function all(): array {
        return $this->symbols;
    }
}
