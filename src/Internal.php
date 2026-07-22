<?php

declare(strict_types=1);

namespace PHireScript;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Internal
{
    public function __construct(private readonly Compiler $compiler)
    {
    }

    public function createPHireScriptInternals(): void
    {
        $config    = $this->compiler->loader->getConfigFile();
        $distPath  = rtrim((string) ($config['paths']['dist'] ?? ''), '/');
        $namespace = (string) ($config['namespace'] ?? 'PHireScript');
        $phpShort  = (string) ($config['phpShort'] ?? '');

        $compilerSrc  = dirname(__DIR__) . '/src';
        $versionedDir = $compilerSrc . '/Internal/PHP' . $phpShort;
        $defaultDir   = $compilerSrc . '/Internal/Default';
        $internalSrc  = is_dir($versionedDir) ? $versionedDir : $defaultDir;

        $destInternals = $distPath . '/Internal';

        $this->publishDirectory($internalSrc, $destInternals, $internalSrc, $namespace);
        $this->publishRuntimeTypes($compilerSrc, $distPath, $namespace);
    }

    private function publishDirectory(string $srcDir, string $destDir, string $internalRoot, string $namespace): void
    {
        if (!is_dir($srcDir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        /** @var SplFileInfo $item */
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            if ($item->getExtension() !== 'php') {
                continue;
            }

            $relativePath = ltrim(substr($item->getPathname(), strlen($internalRoot)), '/');
            $destFile     = $destDir . '/' . $relativePath;

            $this->ensureDir(dirname($destFile));

            $content    = (string) file_get_contents($item->getPathname());
            $newContent = $this->rewriteInternalNamespace($content, $relativePath, $namespace);

            file_put_contents($destFile, $newContent);
        }
    }

    private function rewriteInternalNamespace(string $content, string $relativePath, string $namespace): string
    {
        // relativePath e.g. "Types/ArrayFunctions.php" → sub-namespace "Types"
        $dir         = dirname($relativePath);
        $subNamespace = $dir !== '.' ? '\\' . str_replace('/', '\\', $dir) : '';

        // Old pattern: namespace PHireScript\Internal\{version}\{...};
        // We normalize to:  namespace {namespace}\Internal{subNamespace};
        $newNamespace = $namespace . '\\Internal' . $subNamespace;

        $content = preg_replace(
            '/^namespace\s+[^;]+;/m',
            'namespace ' . $newNamespace . ';',
            $content
        ) ?? $content;

        // Rewrite any use statements that reference the old Internal\{version or Default}\ path
        $content = preg_replace(
            '/\buse\s+[A-Za-z0-9_\\\\]+\\\\Internal\\\\(?:Default|PHP\d+)\\\\/m',
            'use ' . $namespace . '\\Internal\\',
            $content
        ) ?? $content;

        return $content;
    }

    private function publishRuntimeTypes(string $compilerSrc, string $distPath, string $namespace): void
    {
        // Copy abstract base classes (MetaTypes.php, SuperTypes.php) to Internals/
        $baseClasses = ['MetaTypes', 'SuperTypes'];
        foreach ($baseClasses as $baseName) {
            $srcFile  = $compilerSrc . '/Runtime/Types/' . $baseName . '.php';
            $destFile = $distPath . '/Internal/' . $baseName . '.php';

            if (!file_exists($srcFile)) {
                continue;
            }

            $this->ensureDir(dirname($destFile));

            $content    = (string) file_get_contents($srcFile);
            $newContent = preg_replace(
                '/^namespace\s+[^;]+;/m',
                'namespace ' . $namespace . '\\Internal;',
                $content
            ) ?? $content;

            file_put_contents($destFile, $newContent);
        }

        $typesGroups = [
            'MetaTypes'  => $compilerSrc . '/Runtime/Types/MetaTypes',
            'SuperTypes' => $compilerSrc . '/Runtime/Types/SuperTypes',
        ];

        foreach ($typesGroups as $groupName => $srcDir) {
            if (!is_dir($srcDir)) {
                continue;
            }

            $destDir = $distPath . '/Internal/' . $groupName;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            /** @var SplFileInfo $item */
            foreach ($iterator as $item) {
                if ($item->isDir() || $item->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = ltrim(substr($item->getPathname(), strlen($srcDir)), '/');
                $destFile     = $destDir . '/' . $relativePath;

                $this->ensureDir(dirname($destFile));

                $content    = (string) file_get_contents($item->getPathname());
                $newContent = $this->rewriteRuntimeTypeNamespace($content, $groupName, $relativePath, $namespace);

                file_put_contents($destFile, $newContent);
            }
        }
    }

    private function rewriteRuntimeTypeNamespace(
        string $content,
        string $groupName,
        string $relativePath,
        string $namespace
    ): string {
        $dir          = dirname($relativePath);
        $subNamespace = $dir !== '.' ? '\\' . str_replace('/', '\\', $dir) : '';
        $newNamespace = $namespace . '\\Internal\\' . $groupName . $subNamespace;

        // Replace the namespace declaration
        $content = preg_replace(
            '/^namespace\s+[^;]+;/m',
            'namespace ' . $newNamespace . ';',
            $content
        ) ?? $content;

        // Rewrite any use statement referencing Runtime\Types\{MetaTypes|SuperTypes}[\\Anything]
        // Handles same-group and cross-group references (e.g. MetaTypes file using SuperTypes\CardNumber)
        $content = preg_replace_callback(
            '/\buse\s+[A-Za-z0-9_\\\\]+\\\\Runtime\\\\Types\\\\((MetaTypes|SuperTypes)(?:\\\\[A-Za-z0-9_]+)?);/m',
            fn(array $m) => 'use ' . $namespace . '\\Internal\\' . $m[1] . ';',
            $content
        ) ?? $content;

        return $content;
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
