<?php

declare(strict_types=1);

namespace PHPScript;

use PHPScript\Compiler\FileManager;
use PHPScript\Core\CompileMode;
use PHPScript\Core\CompilerContext;
use PHPScript\Transpiler;
use PHPScript\TranspilerDependencyTree;

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

        $listPrograms = $this->loader->load($sourceDir, $transpilerDependencyTree);
        $this->dependencyManager->buildGraph($listPrograms);

        $transpiler = new Transpiler($config, $this->dependencyManager);

        $this->loader->loadAndCompile($sourceDir, $distDir, $transpiler);
    }
}
