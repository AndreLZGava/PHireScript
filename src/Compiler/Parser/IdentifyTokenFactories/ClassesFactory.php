<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\IdentifyTokenFactories\FactoryInitializer;
use PHPScript\Compiler\Parser\Managers\TokenManager;
use PHPScript\Helper\Debug\Debug;

abstract class ClassesFactory extends GlobalFactory
{
    public function getReturnType(): ?string
    {
        $codeBlockToken = $this->returnType();
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
                ->process();

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
                ->process();

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

    public function returnType(): array
    {
        $tokensOfThisBlock = array_slice($this->tokenManager->getTokens(), $this->tokenManager->getCurrentPosition());

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

            if ($capture && $token['type'] === 'T_EOL') {
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
