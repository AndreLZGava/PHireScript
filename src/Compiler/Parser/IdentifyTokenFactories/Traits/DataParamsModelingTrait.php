<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\KeyValuePairNode;
use PHireScript\Compiler\Parser\Ast\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\FactoryInitializer;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

trait DataParamsModelingTrait
{
    public function getArgs($context): array
    {
        $codeBlockToken = $this->codeArgs();

        $factories = FactoryInitializer::getFactories();
        $result = [];
        //Debug::show($this->tokenManager->getCurrentPosition(), $this->tokenManager->getCurrentToken());
        $newTokenManager = new TokenManager($context, $codeBlockToken, 0);

        while (!$newTokenManager->isEndOfTokens()) {
            $token = $newTokenManager->getCurrentToken();
            $returned = (new $factories[$token->type]($newTokenManager, $this->parseContext))
                ->process($this->program);

            if ($returned) {
                $result[] = $returned;
            }

            //$newTokenManager->advance();
        }
        // $this->tokenManager->walk(count($codeBlockToken));
        return $result;
    }

    private function codeArgs(): array
    {
        $openParenthesis = [];
        $closeParenthesis = [];
        $tokensOfThisBlock = array_slice($this->tokenManager->getTokens(), $this->tokenManager->getCurrentPosition());
        foreach ($tokensOfThisBlock as $keyToken => $token) {
            if ($token->value === '(') {
                $openParenthesis[] = $token;
            }

            if ($token->value === ')') {
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
