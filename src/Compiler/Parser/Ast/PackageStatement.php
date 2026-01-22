<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast;

use Exception;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

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
        $basename = basename($this->file);
        $ext = RuntimeClass::DEFAULT_FILE_EXTENSION;

        if (
            !str_starts_with($basename, $this->object) ||
            !str_ends_with($basename, '.' . $ext)
        ) {
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
        $this->completeObjectReference = '\\' . $this->namespace . '\\' . $this->object . '::class';
    }
}
