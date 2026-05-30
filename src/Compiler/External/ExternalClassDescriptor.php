<?php

declare(strict_types=1);

namespace PHireScript\Compiler\External;

final class ExternalClassDescriptor
{
    /**
     * @param array<string, ExternalMemberInfo>   $methods
     * @param array<string, ExternalConstantInfo> $constants
     * @param array<string, ExternalPropertyInfo> $properties
     */
    public function __construct(
        public readonly string $className,
        public readonly string $alias,
        public readonly array $methods,
        public readonly array $constants,
        public readonly ?ExternalConstructorInfo $constructor,
        public readonly array $properties,
    ) {
    }

    public function hasMethod(string $name): bool
    {
        return isset($this->methods[$name]);
    }

    public function hasConstant(string $name): bool
    {
        return isset($this->constants[$name]);
    }

    public function getMethod(string $name): ?ExternalMemberInfo
    {
        return $this->methods[$name] ?? null;
    }

    public function getConstant(string $name): ?ExternalConstantInfo
    {
        return $this->constants[$name] ?? null;
    }
}
