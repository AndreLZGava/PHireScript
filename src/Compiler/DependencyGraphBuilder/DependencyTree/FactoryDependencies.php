<?php

declare(strict_types=1);

namespace PHPScript\Compiler\DependencyGraphBuilder\DependencyTree;

use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\PkgKey;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords\UseKey;
use PHPScript\Compiler\Program;

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
