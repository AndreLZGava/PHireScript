<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Compiler\Parser\Ast\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Ast\ReturnNode;
use PHireScript\Compiler\Parser\Ast\ThisExpressionNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\VariableNode;
use PHireScript\Compiler\Parser\Ast\VoidExpressionNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHireScript\Runtime\RuntimeClass;

class GetterAndSetters extends GlobalFactory
{
    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return  in_array(
            $parseContext->tokenManager->getCurrentToken()->value,
            RuntimeClass::GETTER_AND_SETTER,
            true
        ) &&
            in_array(
                $parseContext->tokenManager->getContext(),
                RuntimeClass::OBJECT_AS_CLASS,
                true
            );
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $node = new MethodDefinition($parseContext->tokenManager->getCurrentToken());
        return $this->parseGetterAndSetter($node, $parseContext);
    }

    private function parseGetterAndSetter(MethodDefinition $node, ParseContext $parseContext)
    {
        $tokens = $parseContext->tokenManager->getLeftTokens();
        $previous = $parseContext->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $parseContext->tokenManager->getCurrentToken();
        $node->modifiers[] = (new ModifiersTransform($parseContext->tokenManager))->map($previous);

        $types = [];
        $name = 'wrongCompilation';
        $typeMethod = '';
        $processBeforeAttribution = true;
        $defaultValue = null;
        foreach ($tokens as $key => $token) {
            if ($processBeforeAttribution && $token->isType()) {
                $types[] = $token->value;
            }

            if ($processBeforeAttribution && $token->isIdentifier()) {
                $name = trim((string) $token->value);
            }

            if ($token->isSymbol() && $token->value === '=') {
                $processBeforeAttribution = false;
                $defaultValue = $tokens[$key + 1];
            }

            if ($token->isEndOfLine()) {
                break;
            }
        }

        if ($currentToken->value === '>') {
            $typeMethod = 'set';
            $arg = new PropertyDefinition($parseContext->tokenManager->getCurrentToken());
            $arg->name = $name;
            $arg->type = implode("|", $types);
            if ($defaultValue) {
                $arg->defaultValue = $defaultValue;
            }
            $parseContext->variables->addProperty($arg);
            $node->args[] = $arg;
            $property = new PropertyAccessNode(
                $currentToken,
                new ThisExpressionNode($currentToken),
                $name
            );
            $assignment = new AssignmentNode($currentToken, $property, new VariableNode($currentToken, $name));
            $node->bodyCode[] = $assignment;
            $returnStatement = new ReturnNode($currentToken, new VoidExpressionNode($currentToken));
            $node->bodyCode[] = $returnStatement;
            $node->returnType = 'Void';
        }

        if ($currentToken->value === '<') {
            $typeMethod = 'get';
            $node->args = [];
            $property = new PropertyAccessNode(
                $currentToken,
                new ThisExpressionNode($currentToken),
                $name
            );
            $returnStatement = new ReturnNode($currentToken, $property);
            $node->bodyCode[] = $returnStatement;

            $node->returnType = implode("|", $types);
        }

        $node->name = $typeMethod . ucfirst($name);
        return $node;
    }
}
