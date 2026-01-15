<?php

declare(strict_types=1);

namespace PHPScript;

use PHPScript\Compiler\Loader;
use PHPScript\Transpiler;
use PHPScript\TranspilerDependencyTree;

class Compiler
{
    private Loader $loader;
    private DependencyGraphBuilder $dependencyManager;
    public function __construct()
    {
        $this->loader = new Loader();
        $this->dependencyManager = new DependencyGraphBuilder();
    }

    public function compile(string $sourceDir, string $distDir)
    {
        $config = $this->loader->getConfigFile();

        $transpilerDependencyTree = new TranspilerDependencyTree($config);

        $listPrograms = $this->loader->load($sourceDir, $transpilerDependencyTree);
        $this->dependencyManager->buildGraph($listPrograms);

        $transpiler = new Transpiler($config, $this->dependencyManager);
        $this->loader->loadAndCompile($sourceDir, $distDir, $transpiler);
    }
}
