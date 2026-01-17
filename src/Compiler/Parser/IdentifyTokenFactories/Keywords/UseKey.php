<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use Exception;
use PHireScript\Compiler\Parser\Ast\DependenciesStatement;
use PHireScript\Compiler\Parser\Ast\DependencyStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class UseKey extends ClassesFactory
{
    public function process(Program $program): ?Node
    {
        $this->program = $program;
        $currentToken = $this->tokenManager->getCurrentToken();
        $this->tokenManager->advance();
        $packages = $this->buildUseNamespaces();
        $packages = new DependenciesStatement(
            $packages,
        );
        $packages->line = $currentToken['line'];
        return $packages;
    }

    private function buildUseNamespaces(): array
    {
        $leftTokens = $this->tokenManager->getLeftTokens();
        $basePath = '';
        $uses = [];
        $walk = 0;

        foreach ($leftTokens as $index => $token) {
            $walk++;

            if ($token['value'] === '{') {
                $groupResults = $this->parseGroup($basePath, $leftTokens, $index);
                $uses = array_merge($uses, $groupResults['uses']);
                $walk += $groupResults['walked'];
                break;
            }

            if ($token['value'] === 'as') {
                $aliasToken = $leftTokens[$index + 1] ?? null;
                if ($aliasToken && $aliasToken['type'] === 'T_IDENTIFIER') {
                    $dependency = new DependencyStatement(rtrim($basePath, '.'));
                    $dependency->alias = $aliasToken['value'];
                    $dependency->line = $token['line'];
                    $uses[] = $dependency;
                    $walk++;
                }
                break;
            }

            if ($token['type'] === 'T_EOL' || $token['value'] === ';') {
                break;
            }

            if ($token['type'] === 'T_IDENTIFIER' || $token['type'] === 'T_SYMBOL' || $token['type'] === 'T_SYMBOL') {
                if (!in_array($token['value'], ['as', '{', '}'])) {
                    $basePath .= $token['value'];
                }
            }
        }

        if (empty($uses) && !empty($basePath)) {
            $dependency = new DependencyStatement(rtrim($basePath, '.'));
            $dependency->line = $leftTokens[0]['line'];
            $uses[] = $dependency;
        }

        if (empty($uses)) {
            throw new Exception('Use statement cannot be empty!');
        }

        $this->tokenManager->walk($walk - 1);
        return $uses;
    }


    private function parseGroup(string $basePath, array $tokens, int $startIndex): array
    {
        $uses = [];
        $walked = 0;
        $currentPath = '';

        for ($i = $startIndex + 1; $i < count($tokens); $i++) {
            $walked++;
            $token = $tokens[$i];

            if ($token['value'] === '}') {
                break;
            }

            if ($token['type'] === 'T_IDENTIFIER') {
                $currentPath = $token['value'];
                $dependency = new DependencyStatement($basePath . $currentPath);
                $dependency->line = $token['line'];

                if (($tokens[$i + 1]['value'] ?? '') === 'as') {
                    $dependency->alias = $tokens[$i + 2]['value'] ?? null;
                    $i += 2;
                    $walked += 2;
                }

                $uses[] = $dependency;
            }
        }

        return ['uses' => $uses, 'walked' => $walked];
    }
}
