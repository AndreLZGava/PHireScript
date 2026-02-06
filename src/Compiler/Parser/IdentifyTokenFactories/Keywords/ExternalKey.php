<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use Exception;
use PHireScript\Compiler\Parser\Ast\ExternalsStatement;
use PHireScript\Compiler\Parser\Ast\NamespaceStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;

class ExternalKey extends ClassesFactory
{
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $this->program = $program;
        $currentToken = $this->tokenManager->getCurrentToken();
        //$this->tokenManager->advance();
        $namespaces = $this->buildUseNamespaces();

        $namespaces = new ExternalsStatement(
            $currentToken,
            $namespaces,
        );
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

            if ($token->value === '{') {
                $groupResults = $this->parseExternalGroup($basePath, $leftTokens, $index);
                $uses = array_merge($uses, $groupResults['uses']);
                $walk += $groupResults['walked'];
                break;
            }

            if ($token->value === 'as') {
                $aliasToken = $leftTokens[$index + 1] ?? null;
                if ($aliasToken && $aliasToken->isIdentifier()) {
                    $dependency = new NamespaceStatement($token, rtrim($basePath, '\\'));
                    $dependency->alias = $aliasToken->value;
                    $uses[] = $dependency;
                    $walk++;
                }
                break;
            }

            $nextToken = $leftTokens[$index + 1] ?? null;
            if (!$nextToken || $nextToken->isEndOfLine() || $nextToken->value === ';') {
                if (empty($uses)) {
                    $basePath .= $token->value;
                    $dependency = new NamespaceStatement($token, rtrim($basePath, '\\'));
                    $uses[] = $dependency;
                }
                break;
            }

            if ($token->isIdentifier() || $token->type === 'T_BACKSLASH') {
                $basePath .= $token->value;
            }
        }

        if (empty($uses)) {
            throw new Exception('External statement cannot be empty!');
        }

        // $this->tokenManager->walk($walk);
        return $uses;
    }

    private function parseExternalGroup(string $basePath, array $tokens, int $startIndex): array
    {
        $uses = [];
        $walked = 0;

        for ($i = $startIndex + 1; $i < count($tokens); $i++) {
            $walked++;
            $token = $tokens[$i];

            if ($token->value === '}') {
                break;
            }

            if ($token->isIdentifier()) {
                $dependency = new NamespaceStatement($token, $basePath . $token->value);

                if (($tokens[$i + 1]->value ?? '') === 'as') {
                    $dependency->alias = $tokens[$i + 2]->value ?? null;
                    $i += 2;
                    $walked += 2;
                }

                $uses[] = $dependency;
            }
        }

        return ['uses' => $uses, 'walked' => $walked];
    }
}
