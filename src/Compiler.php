<?php

declare(strict_types=1);

namespace PHireScript;

use PHireScript\Cache\CacheManager;
use PHireScript\Compiler\FileManager;
use PHireScript\Core\CompileMode;
use PHireScript\Core\CompilerContext;
use PHireScript\Helper\Messenger;
use PHireScript\Runtime\Exceptions\FatalErrorException;
use PHireScript\Runtime\RuntimeClass;
use PHireScript\SymbolTable;
use PHireScript\Transpiler;
use PHireScript\TranspilerDependencyTree;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

class Compiler
{
    private readonly FileManager $loader;
    private readonly DependencyGraphBuilder $dependencyManager;
    private readonly CacheManager $cache;

    public function __construct(private readonly CompilerContext $context)
    {
        $this->cache = new CacheManager(getcwd() ?: '.');
        $this->loader = new FileManager($context, $this->cache);
        $this->dependencyManager = new DependencyGraphBuilder();
    }

    public function compile(?string $sourceDir = null, ?string $distDir = null)
    {
        $startTime = microtime(true);

        set_exception_handler(function (Throwable $e) {
            FatalErrorException::prettyException($e);
        });

        $config = $this->loader->getConfigFile();
        $sourceDir ??= $config['paths']['source'] . '/';
        $distDir ??= $config['paths']['dist'] . '/';
        $this->context->targetWatch = $distDir;

        $sourceFiles = $this->collectSourceFiles($sourceDir);
        $cachedGraph = $this->cache->getDependencyGraph();

        $graphIsUsable = $cachedGraph !== null
            && $this->cache->allFilesValid($sourceFiles)
            && !DependencyGraphBuilder::hasOrphanedNodes($cachedGraph);

        $sharedTable = new SymbolTable();
        $transpiler  = new Transpiler(
            $config,
            $this->dependencyManager,
            $this->context,
            $this->cache,
            $sharedTable,
        );

        /** @var array<string, \PHireScript\Compiler\Program> $parsedPrograms */
        $parsedPrograms = [];

        if ($graphIsUsable) {
            $this->dependencyManager->restoreFromCache($cachedGraph);
        } else {
            if ($cachedGraph !== null) {
                $this->cache->invalidateDependencyGraph();
            }

            // Pre-build pass: light parse (pkg/use only) to register the dependency graph
            // before the full parse. The full parser relies on isDependencyOf() to correctly
            // identify custom types in method signatures; the graph must exist first.
            $depTreeParser = new TranspilerDependencyTree($config, $this->context, $this->cache);
            $preBuildPrograms = [];
            foreach ($sourceFiles as $filePath) {
                $preBuildPrograms[] = $depTreeParser->compile(
                    (string) file_get_contents($filePath),
                    $filePath,
                );
            }
            $this->dependencyManager->buildGraph($preBuildPrograms, $config);
            $this->cache->setDependencyGraph($this->dependencyManager->exportForCache());

            // Full parse pass: dep graph is now populated, custom types resolve correctly.
            foreach ($sourceFiles as $filePath) {
                $parsedPrograms[$filePath] = $transpiler->parseOnly(
                    (string) file_get_contents($filePath),
                    $filePath,
                );
            }
        }

        // Phase 1 (non-watch): bind all files in topological order so that
        // the shared SymbolTable is fully populated before any Checker runs.
        if ($this->context->mode !== CompileMode::WATCH) {
            $packageToFile = $this->dependencyManager->getPackageToFileMap();
            foreach ($this->dependencyManager->getCompilationOrder() as $package) {
                $filePath = $packageToFile[$package] ?? null;
                if ($filePath !== null && is_file($filePath)) {
                    if (isset($parsedPrograms[$filePath])) {
                        // Graph-miss path: AST already in memory, just bind it.
                        $transpiler->bindProgram($parsedPrograms[$filePath], $filePath);
                    } else {
                        // Graph-hit path: parse + bind from disk.
                        $transpiler->parseAndBind((string) file_get_contents($filePath), $filePath);
                    }
                }
            }
        }

        $this->loader->loadAndCompile($sourceDir, $distDir, $transpiler, $this->dependencyManager);

        $this->cache->close();

        $elapsedMs = (int) round((microtime(true) - $startTime) * 1000);
        $peakMemory = Messenger::formatBytes(memory_get_peak_usage(true));
        Messenger::muted("Done in {$elapsedMs}ms · peak memory: {$peakMemory}");
    }

    /**
     * Collect all compilable source files (.ps / .pst) in the given directory.
     *
     * @return array<int, string>
     */
    private function collectSourceFiles(string $sourceDir): array
    {
        $files    = [];
        $allowed  = [RuntimeClass::DEFAULT_FILE_EXTENSION, RuntimeClass::DEFAULT_FILE_TEST_EXTENSION];
        $directory = new RecursiveDirectoryIterator(
            $sourceDir,
            RecursiveDirectoryIterator::SKIP_DOTS,
        );
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            if (!in_array($file->getExtension(), $allowed, true)) {
                continue;
            }

            $realPath = $file->getRealPath();

            if ($realPath !== false) {
                $files[] = $realPath;
            }
        }

        return $files;
    }
}
