<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use Exception;
use PHPScript\Compiler\Parser\Ast\DependenciesStatement;
use PHPScript\Compiler\Parser\Ast\DependencyStatement;
use PHPScript\Compiler\Parser\Ast\ExternalsStatement;
use PHPScript\Compiler\Parser\Ast\NamespaceStatement;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHPScript\Compiler\Program;
use PHPScript\Helper\Debug\Debug;

class ExternalKey extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $currentToken = $this->tokenManager->getCurrentToken();
        $this->tokenManager->advance();
        $namespaces = $this->buildUseNamespaces();

        $namespaces = new ExternalsStatement(
            $namespaces,
        );
        $namespaces->line = $currentToken['line'];
        return $namespaces;
    }

    private function buildUseNamespaces(): array
    {
        $leftTokens = $this->tokenManager->getLeftTokens();
        $use = '';
        $uses = [];
        $processSingleDependency = true;
        $processingSubGroup = false;
        $walk = 0;
        foreach ($leftTokens as $keyToken => $token) {
            $walk++;
            if (
                !$processingSubGroup
            ) {
                if (
                    $token['type'] === 'T_IDENTIFIER' ||
                    $token['type'] === 'T_BACKSLASH'
                ) {
                    $use .= $token['value'];
                }
            }

            if (
                $token['type'] === 'T_SYMBOL' &&
                $token['value'] === '{' ||
                $token['value'] === ','
            ) {
                $processingSubGroup = true;
                $processSingleDependency = false;
                $package = new NamespaceStatement(
                    $use . $leftTokens[$keyToken + 1]['value'],
                );
                $package->line = $token['line'];

                if (
                    $leftTokens[$keyToken + 2]['value'] === 'as' &&
                    $leftTokens[$keyToken + 3]['type'] === 'T_IDENTIFIER'
                ) {
                      $package->alias = $leftTokens[$keyToken + 3]['value'];
                }

                $uses[] = $package;
                continue;
            }

            if ($leftTokens[$keyToken + 1]['type'] === 'T_EOL' || $token['value'] === '}') {
                if ($processSingleDependency) {
                    $dependency = new NamespaceStatement(
                        $use,
                    );
                    $dependency->line = $token['line'];
                    $uses[] = $dependency;
                }
                break;
            }
        }

        if (empty($uses)) {
            throw new Exception('external can\' be empty!');
        }
        $this->tokenManager->walk($walk);
        return $uses;
    }
}
