<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods;

use Closure;

class BaseRegistryFunctions
{
    public function __construct(
        public string $className,
        public string $name,
        public ?Closure $function = null,
        public ?string $parentClass = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function __serialize(): array
    {
        return [
            'className'   => $this->className,
            'name'        => $this->name,
            'parentClass' => $this->parentClass,
        ];
    }

    /** @param array<string, mixed> $data */
    public function __unserialize(array $data): void
    {
        $this->className   = is_string($data['className']) ? $data['className'] : '';
        $this->name        = is_string($data['name']) ? $data['name'] : '';
        $this->parentClass = is_string($data['parentClass'] ?? null) ? $data['parentClass'] : null;
        $this->function    = null;
    }
}
