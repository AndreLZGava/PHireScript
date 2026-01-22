<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use Exception;
use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\ComplexObjectDefinition;
use PHireScript\Compiler\Parser\Ast\ConstructorDefinition;
use PHireScript\Compiler\Parser\Ast\IfStatementNode;
use PHireScript\Compiler\Parser\Ast\IssetOperatorNode;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\NewExceptionNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\NotOperatorNode;
use PHireScript\Compiler\Parser\Ast\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Ast\ThisExpressionNode;
use PHireScript\Compiler\Parser\Ast\ThrowStatementNode;
use PHireScript\Compiler\Parser\Ast\TraitDefinition;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\FactoryInitializer;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

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
        //        Debug::show($codeBlockToken);exit;
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

    public function getExtends(mixed $node): ?string
    {
        $extends = null;
        $left = $this->tokenManager->getLeftTokens();
        foreach ($left as $tokenId => $tokens) {
            if (
                $tokens['value'] === 'extends' &&
                $left[$tokenId + 1]['type'] === 'T_IDENTIFIER'
            ) {
                $extends = $left[$tokenId + 1]['value'];
                break;
            }
        }
        return $extends;
    }

    public function getContentBlock(ComplexObjectDefinition $node): array
    {
        $codeBlockToken = $this->codeBlockToken();

        $factories = FactoryInitializer::getFactories();
        $result = [];

        $newTokenManager = new TokenManager($node->type, $codeBlockToken, 0);
        //Debug::show($codeBlockToken);exit;
        while (!$newTokenManager->isEndOfTokens()) {
            $token = $newTokenManager->getCurrentToken();
            //Debug::show($token);
            $returned = (new $factories[$token['type']]($newTokenManager))
                ->process($this->program);

            if ($returned) {
                if ($node instanceof ClassDefinition) {
                    $this->processConstruct($node, $returned);
                }
                $result[] = $returned;
            }

            $newTokenManager->advance();
        }

        $this->tokenManager->walk(count($codeBlockToken));

        return $result;
    }

    private function processConstruct(ClassDefinition $node, mixed $processedNode)
    {
        if (
            $processedNode instanceof PropertyDefinition &&
            in_array('abstract', $processedNode->modifiers)
        ) {
            $constructor = $node->construct ?? new ConstructorDefinition();
            $constructor->line = $processedNode->line;

            $validationNode = new IfStatementNode(
                condition: new NotOperatorNode(
                    new IssetOperatorNode(
                        new PropertyAccessNode(
                            new ThisExpressionNode(),
                            $processedNode->name
                        )
                    )
                ),
                statements: new ThrowStatementNode(
                    new NewExceptionNode(
                        'LogicException',
                        "Property {$processedNode->name} must be initialized."
                    )
                )
            );

            $constructor->body[] = $validationNode;

            $node->construct = $constructor;
        }
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
        $isClass = in_array($this->tokenManager->getContext(), ['class', 'trait']);
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
