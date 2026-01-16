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

            // 1. Se encontrar '{', processa o grupo e encerra o loop principal
            if ($token['value'] === '{') {
                $groupResults = $this->parseExternalGroup($basePath, $leftTokens, $index);
                $uses = array_merge($uses, $groupResults['uses']);
                $walk += $groupResults['walked'];
                break;
            }

            // 2. Se encontrar 'as', captura o alias e para
            if ($token['value'] === 'as') {
                $aliasToken = $leftTokens[$index + 1] ?? null;
                if ($aliasToken && $aliasToken['type'] === 'T_IDENTIFIER') {
                    $dependency = new NamespaceStatement(rtrim($basePath, '\\'));
                    $dependency->alias = $aliasToken['value'];
                    $dependency->line = $token['line'];
                    $uses[] = $dependency;
                    $walk++; // Avança para cobrir o nome do alias
                }
                break; // IMPORTANTE: Para aqui para não ler o resto da linha
            }

            // 3. Verifica se o PRÓXIMO token indica fim de instrução
            // Isso evita que o 'walk' consuma o T_EOL ou o ';' que pertence ao sistema
            $nextToken = $leftTokens[$index + 1] ?? null;
            if (!$nextToken || $nextToken['type'] === 'T_EOL' || $nextToken['value'] === ';') {
                // Se chegamos aqui, é um uso simples (sem 'as' e sem '{}')
                if (empty($uses)) {
                    $basePath .= $token['value']; // Adiciona o último identificador
                    $dependency = new NamespaceStatement(rtrim($basePath, '\\'));
                    $dependency->line = $token['line'];
                    $uses[] = $dependency;
                }
                break;
            }

            // 4. Constrói o path
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
