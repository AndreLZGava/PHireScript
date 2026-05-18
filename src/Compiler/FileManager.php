<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use Exception;
use PHireScript\Compiler\FileManager\ClassScanner;
use PHireScript\Compiler\FileManager\ErrorRenderer;
use PHireScript\Compiler\FileManager\FileCompiler;
use PHireScript\Compiler\FileManager\FileWatcher;
use PHireScript\Core\CompileMode;
use PHireScript\Core\CompilerContext;
use PHireScript\Helper\Messenger;
use PHireScript\Runtime\RuntimeClass;
use PHireScript\Runtime\Types\MetaTypes;
use PHireScript\Runtime\Types\SuperTypes;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FileManager
{
    private readonly ClassScanner $classScanner;
    private readonly ErrorRenderer $errorRenderer;
    private readonly FileCompiler $fileCompiler;
    private readonly FileWatcher $fileWatcher;

    public function __construct(private readonly CompilerContext $context)
    {
        $this->classScanner  = new ClassScanner();
        $this->errorRenderer = new ErrorRenderer();
        $this->fileCompiler  = new FileCompiler($context, $this->errorRenderer);
        $this->fileWatcher   = new FileWatcher($context, $this->fileCompiler);
    }

    public function loadAndCompile(string $sourceDir, string $distDir, mixed $transpiler): void
    {
        if ($this->context->mode === CompileMode::WATCH) {
            $this->fileWatcher->watch($sourceDir, $distDir, $transpiler);
        }

        $directory = new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator  = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $relativePath = substr((string) $file->getPathname(), strlen($sourceDir));
            $extension    = $file->getExtension();

            if (
                $this->context->file === $file->getPathname() ||
                empty($this->context->file)
            ) {
                if ($extension === RuntimeClass::DEFAULT_FILE_EXTENSION) {
                    $outputFile = $distDir . str_replace(
                        '.' . $extension,
                        '.php',
                        $relativePath
                    );
                    $this->fileCompiler->compileFile((string) $file->getPathname(), $outputFile, $transpiler);
                } elseif ($extension === RuntimeClass::DEFAULT_FILE_TEST_EXTENSION) {
                    $outputFile = $distDir . str_replace(
                        '.' . $extension,
                        'Test.php',
                        $relativePath
                    );
                    $this->fileCompiler->compileFile((string) $file->getPathname(), $outputFile, $transpiler);
                } else {
                    $this->fileCompiler->copyFile((string) $file->getPathname(), $distDir . $relativePath);
                }
            }
        }
    }

    /** @return array<int, mixed> */
    public function load(string $sourceDir, mixed $transpiler): array
    {
        $directory = new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator  = new RecursiveIteratorIterator($directory);
        $allowed   = [
            RuntimeClass::DEFAULT_FILE_EXTENSION,
            RuntimeClass::DEFAULT_FILE_TEST_EXTENSION,
        ];
        $result = [];

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if (!\in_array($file->getExtension(), $allowed, true)) {
                continue;
            }

            $sourceCode = '';

            try {
                $sourceCode = (string) file_get_contents($file->getPathname());
                $result[]   = $transpiler->compile($sourceCode, $file->getPathname());
            } catch (Exception $e) {
                Messenger::error(
                    "Error in {$file->getPathname()}: " . $e->getMessage(),
                    true
                );
                $this->errorRenderer->renderCli($e, $transpiler, $sourceCode);
            }
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function getConfigFile(): array
    {
        /** @var array<string, mixed> $configs */
        $configs               = json_decode((string) file_get_contents('PHireScript.json'), true);
        $configs['php']        = phpversion();
        $configs['metatypes']  = $this->classScanner->listClassesExtending(
            __DIR__ . '/../../src/Runtime/Types/MetaTypes/',
            MetaTypes::class
        );
        $configs['supertypes'] = $this->classScanner->listClassesExtending(
            __DIR__ . '/../../src/Runtime/Types/SuperTypes/',
            SuperTypes::class
        );

        return $configs;
    }
}
