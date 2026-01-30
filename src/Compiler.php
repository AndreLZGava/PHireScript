<?php

declare(strict_types=1);

namespace PHireScript;

use PHireScript\Compiler\FileManager;
use PHireScript\Core\CompileMode;
use PHireScript\Core\CompilerContext;
use PHireScript\Transpiler;
use PHireScript\TranspilerDependencyTree;

class Compiler
{
    private FileManager $loader;
    private DependencyGraphBuilder $dependencyManager;
    public function __construct(CompilerContext $context)
    {
        $this->loader = new FileManager($context);
        $this->dependencyManager = new DependencyGraphBuilder();
    }

    public function compile(?string $sourceDir = null, ?string $distDir = null)
    {
        $config = $this->loader->getConfigFile();
        $sourceDir = $sourceDir ?? $config['paths']['source'] . '/';
        $distDir = $distDir ?? $config['paths']['dist'] . '/';

        $transpilerDependencyTree = new TranspilerDependencyTree($config);

        //$listPrograms = $this->loader->load($sourceDir, $transpilerDependencyTree);
        // $this->dependencyManager->buildGraph($listPrograms);

        $transpiler = new Transpiler($config, $this->dependencyManager);

        $this->loader->loadAndCompile($sourceDir, $distDir, $transpiler);
    }
}
