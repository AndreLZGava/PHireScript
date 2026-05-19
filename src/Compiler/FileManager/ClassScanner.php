<?php

declare(strict_types=1);

namespace PHireScript\Compiler\FileManager;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;

class ClassScanner
{
    /** @var array<string, array<int, string>> In-process cache keyed by "directory:baseClass". */
    private static array $memo = [];

    /** @return array<int, string> */
    public function listClassesExtending(string $directory, string $baseClass): array
    {
        $key = $directory . ':' . $baseClass;

        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        $directory = realpath($directory);

        if ($directory === false || !is_dir($directory)) {
            throw new RuntimeException("Invalid path: {$directory}");
        }

        if (!class_exists($baseClass) && !interface_exists($baseClass)) {
            throw new RuntimeException("Base class does not exists: {$baseClass}");
        }

        $classes = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $tokens  = token_get_all((string) $content);

            $namespace = '';
            $class     = null;

            for ($i = 0; $i < \count($tokens); $i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    $namespace = '';
                    for ($j = $i + 2; isset($tokens[$j]); $j++) {
                        if ($tokens[$j] === ';') {
                            break;
                        }
                        $namespace .= $tokens[$j][1];
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    $class = $tokens[$i + 2][1] ?? null;

                    for ($j = $i; isset($tokens[$j]); $j++) {
                        if (\is_array($tokens[$j]) && $tokens[$j][0] === T_EXTENDS) {
                            break;
                        }
                    }
                    break;
                }
            }

            if (!$class) {
                continue;
            }

            $fqcn = $namespace ? "$namespace\\$class" : $class;

            if (!class_exists($fqcn)) {
                require_once $file->getPathname();
            }

            if (!class_exists($fqcn)) {
                continue;
            }

            $ref = new ReflectionClass($fqcn);

            if ($ref->isSubclassOf($baseClass)) {
                $classes[] = $fqcn;
            }
        }

        self::$memo[$key] = $classes;

        return $classes;
    }
}
