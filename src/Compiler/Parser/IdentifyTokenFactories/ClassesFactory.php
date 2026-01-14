<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use Exception;
use PHPScript\Compiler\Parser\Ast\MethodDefinition;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\IdentifyTokenFactories\FactoryInitializer;
use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Compiler\Program;
use PHPScript\Helper\Debug\Debug;
use PHPScript\Runtime\RuntimeClass;

abstract class ClassesFactory extends GlobalFactory
{
    protected Program $program;
    public function getMethodBody(MethodDefinition $node): array
    {
        $codeBlockToken = $this->codeBlockToken();
        $factories = FactoryInitializer::getFactories();
        $result = [];
        //Debug::show($this->tokenManager->getCurrentPosition(), $this->tokenManager->getCurrentToken());
        $newTokenManager = new TokenManager(RuntimeClass::CONTEXT_GET_BODY_METHOD, $codeBlockToken, 0);

        while (!$newTokenManager->isEndOfTokens()) {
            $token = $newTokenManager->getCurrentToken();
            $returned = (new $factories[$token['type']]($newTokenManager))
                ->process($this->program);

            if ($returned) {
                //  Debug::show($token);
                $result[] = $returned;
            }

            $newTokenManager->advance();
        }
        //Debug::show($codeBlockToken, $this->tokenManager->getTokens());exit;
        $this->tokenManager->walk(count($codeBlockToken));
        return $result;
    }

    public function getReturnType(MethodDefinition $node): ?string
    {
        $codeBlockToken = $this->returnType($node);
        $result = '';
        foreach ($codeBlockToken as $tokens) {
            if (!empty($tokens)) {
                $result .= $tokens['value'];
            }
        }
        $this->tokenManager->walk(count($codeBlockToken));
        return $result;
    }

    public function getArgs($context): array
    {
        $codeBlockToken = $this->codeArgs();

        $factories = FactoryInitializer::getFactories();
        $result = [];
        //Debug::show($this->tokenManager->getCurrentPosition(), $this->tokenManager->getCurrentToken());
        $newTokenManager = new TokenManager($context, $codeBlockToken, 0);

        while (!$newTokenManager->isEndOfTokens()) {
            $token = $newTokenManager->getCurrentToken();
            $returned = (new $factories[$token['type']]($newTokenManager))
                ->process($this->program);

            if ($returned) {
                //  Debug::show($token);
                $result[] = $returned;
            }

            $newTokenManager->advance();
        }
        //Debug::show($codeBlockToken, $this->tokenManager->getTokens());exit;
        $this->tokenManager->walk(count($codeBlockToken));
        return $result;
    }

    public function getContentBlock($context): array
    {
        $codeBlockToken = $this->codeBlockToken();

        $factories = FactoryInitializer::getFactories();
        $result = [];

        $newTokenManager = new TokenManager($context, $codeBlockToken, 0);
        //Debug::show($codeBlockToken);exit;
        while (!$newTokenManager->isEndOfTokens()) {
            $token = $newTokenManager->getCurrentToken();
            //Debug::show($token);
            $returned = (new $factories[$token['type']]($newTokenManager))
                ->process($this->program);

            if ($returned) {
                $result[] = $returned;
            }

            $newTokenManager->advance();
        }

        $this->tokenManager->walk(count($codeBlockToken));

        return $result;
    }

    public function codeBlockToken(): array
    {
        $openBrackets = [];
        $closeBrackets = [];
        $tokensOfThisBlock = array_slice($this->tokenManager->getTokens(), $this->tokenManager->getCurrentPosition());

        foreach ($tokensOfThisBlock as $keyToken => $token) {
            if ($token['value'] === '{') {
                $openBrackets[] = $token;
            }

            if ($token['value'] === '}') {
                $closeBrackets[] = $token;
                if (count($openBrackets) === count($closeBrackets)) {
                    break;
                }
            }
        }

        $tokensOfThisBlock = array_slice($tokensOfThisBlock, 0, $keyToken + 1);

        return $tokensOfThisBlock;
    }

    public function returnType(MethodDefinition $node): array
    {
        $tokensOfThisBlock = array_slice($this->tokenManager->getTokens(), $this->tokenManager->getCurrentPosition());
        if (in_array($this->tokenManager->getCurrentToken()['type'], ['T_EOL', 'T_COMMENT'], true)) {
            throw new Exception('Method ' . $node->name . ' has no definition ' .
                'of return. Please implement a return explicitly!');
        }
        $isClass = $this->tokenManager->getContext() === 'class';

        $result = [];
        $capture = false;

        foreach ($tokensOfThisBlock as $token) {
            if (!$capture) {
                $result[] = false;
            }

            if ($token['type'] === 'T_SYMBOL' && $token['value'] === ':') {
                $capture = true;
                continue;
            }

            if (
                $capture &&
                $token['type'] === 'T_EOL' ||
                $isClass &&
                $token['type'] === 'T_SYMBOL' && $token['value'] === '{'
            ) {
                break;
            }

            if ($capture) {
                $result[] = $token;
            }
        }
        return $result;
    }

    public function codeArgs(): array
    {
        $openParenthesis = [];
        $closeParenthesis = [];
        $tokensOfThisBlock = array_slice($this->tokenManager->getTokens(), $this->tokenManager->getCurrentPosition());
        foreach ($tokensOfThisBlock as $keyToken => $token) {
            if ($token['value'] === '(') {
                $openParenthesis[] = $token;
            }

            if ($token['value'] === ')') {
                $closeParenthesis[] = $token;
                if (count($openParenthesis) === count($closeParenthesis)) {
                    break;
                }
            }
        }

        $tokensOfThisBlock = array_slice($tokensOfThisBlock, 0, $keyToken + 1);

        return $tokensOfThisBlock;
    }
}
