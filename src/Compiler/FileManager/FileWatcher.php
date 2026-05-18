<?php

declare(strict_types=1);

namespace PHireScript\Compiler\FileManager;

use PHireScript\Core\CompilerContext;
use PHireScript\Helper\Messenger;
use PHireScript\Runtime\RuntimeClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

class FileWatcher
{
    public function __construct(
        private readonly CompilerContext $context,
        private readonly FileCompiler $fileCompiler,
    ) {
    }

    public function watch(string $sourceDir, string $distDir, mixed $transpiler): never
    {
        $extensionsToWatch = $this->context->getExtensionToPersist();
        $targetDir         = $this->context->targetWatch;

        Messenger::info("PHireScript started", true);
        $watching = implode(', ', $extensionsToWatch);
        Messenger::text("Watching files: .{$watching} in: {$targetDir}");

        $filesHash = [];

        while (true) {
            try {
                $directory = new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS);
                $iterator  = new RecursiveIteratorIterator($directory);

                foreach ($iterator as $file) {
                    /** @var SplFileInfo $file */
                    if (!$file->isFile()) {
                        continue;
                    }

                    $filePath      = (string) $file->getRealPath();
                    $relativePath  = \substr((string) $file->getPathname(), \strlen($sourceDir) + 1);
                    $fileExtension = $file->getExtension();

                    $isNotWatchedExtension = !\in_array($fileExtension, $extensionsToWatch, true);
                    $currentHash           = $isNotWatchedExtension
                        ? \filemtime($filePath)
                        : \md5_file($filePath);

                    try {
                        if (!isset($filesHash[$filePath]) || $filesHash[$filePath] !== $currentHash) {
                            if ($isNotWatchedExtension) {
                                $outputFile = $distDir . '/' . $relativePath;
                                $this->fileCompiler->copyFile((string) $file->getPathname(), $outputFile);
                                $filesHash[$filePath] = $currentHash;
                                continue;
                            }

                            $outputFile = null;

                            if ($fileExtension === RuntimeClass::DEFAULT_FILE_EXTENSION) {
                                $outputFile = $distDir . '/' . str_replace(
                                    '.' . $fileExtension,
                                    '.php',
                                    $relativePath
                                );
                            } elseif ($fileExtension === RuntimeClass::DEFAULT_FILE_TEST_EXTENSION) {
                                continue;
                            }

                            if ($outputFile === null) {
                                continue;
                            }

                            $outputSubDir = dirname($outputFile);

                            if (!is_dir($outputSubDir)) {
                                mkdir($outputSubDir, 0755, true);
                            }

                            if (isset($filesHash[$filePath])) {
                                Messenger::info("[" . date('H:i:s') . "] Changes detected: {$filePath}", true);
                                $this->fileCompiler->compileFile(
                                    (string) $file->getPathname(),
                                    $outputFile,
                                    $transpiler,
                                );
                            } else {
                                Messenger::text("[" . date('H:i:s') . "] Watching: {$filePath}");
                            }

                            $filesHash[$filePath] = $currentHash;
                        }
                    } catch (Throwable $e) {
                        Messenger::error(
                            "[" . date('H:i:s') . "] Error processing {$filePath}: " . $e->getMessage(),
                            true
                        );
                    }
                }
            } catch (Throwable $e) {
                Messenger::error("[" . date('H:i:s') . "] Watcher error: " . $e->getMessage(), true);
            }

            clearstatcache();
            usleep(900000);
        }
    }
}
