<?php

declare(strict_types=1);

namespace PHireScript;

use PHireScript\Compiler\FileManager;
use PHireScript\Core\CompilerContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Helper\Messenger;
use PHireScript\Runtime\Exceptions\FatalErrorException;
use PHireScript\Transpiler;
use PHireScript\TranspilerDependencyTree;
use Throwable;

class Compiler
{
    private readonly FileManager $loader;
    private readonly DependencyGraphBuilder $dependencyManager;
    public function __construct(private readonly CompilerContext $context)
    {
        $this->loader = new FileManager($context);
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
        $transpilerDependencyTree = new TranspilerDependencyTree($config, $this->context);

        $listPrograms = $this->loader->load($sourceDir, $transpilerDependencyTree);
        $this->dependencyManager->buildGraph($listPrograms, $config);
        $transpiler = new Transpiler($config, $this->dependencyManager, $this->context);
        $this->loader->loadAndCompile($sourceDir, $distDir, $transpiler);

        $elapsedMs = (int) round((microtime(true) - $startTime) * 1000);
        $peakMemory = Messenger::formatBytes(memory_get_peak_usage(true));
        Messenger::muted("Done in {$elapsedMs}ms · peak memory: {$peakMemory}");
    }
}
