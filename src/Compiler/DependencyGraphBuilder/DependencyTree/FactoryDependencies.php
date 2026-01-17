<?php

declare(strict_types=1);

namespace PHireScript\Compiler\DependencyGraphBuilder\DependencyTree;

use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\PkgKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\UseKey;
use PHireScript\Compiler\Program;

class FactoryDependencies
{
    public static function getFactories(TokenManager $tokenManager, Program $program): mixed
    {
        $factories = [
        'pkg' => PkgKey::class,
        'use' => UseKey::class,
        ];

        $tokenValue = $tokenManager->getCurrentToken()['value'];

        if (!isset($factories[$tokenValue])) {
            return null;
        }

        $processor = new $factories[$tokenValue]($tokenManager);

        return $processor->process($program);
    }
}
