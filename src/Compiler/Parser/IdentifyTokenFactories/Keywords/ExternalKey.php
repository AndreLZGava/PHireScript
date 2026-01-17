<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use Exception;
use PHPScript\Compiler\Parser\Ast\ExternalsStatement;
use PHPScript\Compiler\Parser\Ast\NamespaceStatement;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHPScript\Compiler\Program;

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
        $basePath = '';
        $uses = [];
        $walk = 0;

        foreach ($leftTokens as $index => $token) {
            $walk++;

            if ($token['value'] === '{') {
                $groupResults = $this->parseExternalGroup($basePath, $leftTokens, $index);
                $uses = array_merge($uses, $groupResults['uses']);
                $walk += $groupResults['walked'];
                break;
            }

            if ($token['value'] === 'as') {
                $aliasToken = $leftTokens[$index + 1] ?? null;
                if ($aliasToken && $aliasToken['type'] === 'T_IDENTIFIER') {
                    $dependency = new NamespaceStatement(rtrim($basePath, '\\'));
                    $dependency->alias = $aliasToken['value'];
                    $dependency->line = $token['line'];
                    $uses[] = $dependency;
                    $walk++;
                }
                break;
            }

            $nextToken = $leftTokens[$index + 1] ?? null;
            if (!$nextToken || $nextToken['type'] === 'T_EOL' || $nextToken['value'] === ';') {
                if (empty($uses)) {
                    $basePath .= $token['value'];
                    $dependency = new NamespaceStatement(rtrim($basePath, '\\'));
                    $dependency->line = $token['line'];
                    $uses[] = $dependency;
                }
                break;
            }

            if ($token['type'] === 'T_IDENTIFIER' || $token['type'] === 'T_BACKSLASH') {
                $basePath .= $token['value'];
            }
        }

        if (empty($uses)) {
            throw new Exception('External statement cannot be empty!');
        }

        $this->tokenManager->walk($walk);
        return $uses;
    }

    private function parseExternalGroup(string $basePath, array $tokens, int $startIndex): array
    {
        $uses = [];
        $walked = 0;

        for ($i = $startIndex + 1; $i < count($tokens); $i++) {
            $walked++;
            $token = $tokens[$i];

            if ($token['value'] === '}') {
                break;
            }

            if ($token['type'] === 'T_IDENTIFIER') {
                $dependency = new NamespaceStatement($basePath . $token['value']);
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
