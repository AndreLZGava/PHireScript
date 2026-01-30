<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords;

use Exception;
use PHireScript\Compiler\Parser\Ast\DependenciesStatement;
use PHireScript\Compiler\Parser\Ast\DependencyStatement;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\ClassesFactory;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class UseKey extends ClassesFactory
{
    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $this->program = $program;
        $currentToken = $this->tokenManager->getCurrentToken();
        $this->tokenManager->advance();
        $packages = $this->buildUseNamespaces();
        $packages = new DependenciesStatement(
            $currentToken,
            $packages,
        );
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

            if ($token->value === '{') {
                $groupResults = $this->parseGroup($basePath, $leftTokens, $index);
                $uses = array_merge($uses, $groupResults['uses']);
                $walk += $groupResults['walked'];
                break;
            }

            if ($token->value === 'as') {
                $aliasToken = $leftTokens[$index + 1] ?? null;
                if ($aliasToken && $aliasToken->isIdentifier()) {
                    $dependency = new DependencyStatement($token, rtrim($basePath, '.'));
                    $dependency->alias = $aliasToken->value;
                    $uses[] = $dependency;
                    $walk++;
                }
                break;
            }

            if ($token->isEndOfLine() || $token->value === ';') {
                break;
            }

            if ($token->isIdentifier() || $token->isSymbol() || $token->isSymbol()) {
                if (!in_array($token->value, ['as', '{', '}'])) {
                    $basePath .= $token->value;
                }
            }
        }

        if (empty($uses) && !empty($basePath)) {
            $dependency = new DependencyStatement($leftTokens[0], rtrim($basePath, '.'));
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

            if ($token->value === '}') {
                break;
            }

            if ($token->isIdentifier()) {
                $currentPath = $token->value;
                $dependency = new DependencyStatement($token, $basePath . $currentPath);

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
