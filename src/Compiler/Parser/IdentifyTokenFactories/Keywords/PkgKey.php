<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use Exception;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\PackageStatement;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

class PkgKey extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $currentToken = $this->tokenManager->getCurrentToken();
        $object = $this->getObjectAssign();
        $package = $this->getPkg();
        $package = new PackageStatement(
            $package,
            $object,
            $program->path,
        );
        $package->line = $currentToken['line'];
        $package->generateNamespace($program->config);
        return $package;
    }

    private function getObjectAssign(): string
    {
        $leftTokens = $this->tokenManager->getLeftTokens();
        $objects = RuntimeClass::OBJECT_AS_CLASS;
        foreach ($leftTokens as $keyToken => $token) {
            if (
                $token['type'] === 'T_KEYWORD' &&
                in_array($token['value'], $objects, true) &&
                $leftTokens[$keyToken + 1]['type'] === 'T_IDENTIFIER'
            ) {
                return $leftTokens[$keyToken + 1]['value'];
            }
        }
        throw new Exception('Could not load ' . implode(', ', $objects));
    }

    private function getPkg()
    {
        $package = '';
        $walk = 0;
        $leftTokens = $this->tokenManager->getLeftTokens();
        foreach ($leftTokens as $keyToken => $token) {
            $walk++;
            if (
                $token['type'] === 'T_EOL' ||
                $token['type'] === 'T_SYMBOL' &&
                $leftTokens[$keyToken + 1]['type'] === 'T_EOL'
            ) {
                break;
            }

            if ($token['type'] === 'T_IDENTIFIER') {
                $package .= $token['value'];
            }

            if ($token['type'] === 'T_SYMBOL' && $token['value'] === '.') {
                $package .= $token['value'];
            }
        }
        return $package;
    }
}
