<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\Ast;

use Exception;
use PHPScript\Helper\Debug\Debug;

class PackageStatement extends Statement
{
    public readonly string $namespace;
    public readonly string $completeObjectReference;

    public readonly string $completePackage;

    public function __construct(
        public readonly string $package,
        public readonly string $object,
        public readonly string $file,
    ) {
        $this->validate();
        $this->completePackage = $package . '.' . $object;
    }

    private function validate()
    {
        if (!str_contains($this->file, $this->object . '.ps')) {
            throw new Exception('File name must match class/interface/type/' .
            'immutable/trait name! File ' . $this->file . ' object name '
            . $this->object);
        }
    }

    public function generateNamespace(array $config): void
    {
        $namespace = '';
        $namespace = current(explode('/' . $this->object, $this->file));
        $baseDir = rtrim((string) $config['paths']['source'], '/') . '/';

        if (str_starts_with($namespace, $baseDir)) {
            $namespace = substr($namespace, strlen($baseDir));
        }

        $namespace = str_replace('/', '\\', $namespace);

        $this->namespace = $config['namespace'] . '\\' . $namespace;
        $this->completeObjectReference = '\\' . $this->namespace . '::class';
    }
}
