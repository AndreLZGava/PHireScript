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

class ComplexObject extends GlobalFactory
{
    public function isTheCase()
    {
        return  in_array($this->tokenManager->getCurrentToken()->value, RuntimeClass::ACCESSORS, true) &&
        in_array($this->tokenManager->getContext(), RuntimeClass::OBJECT_AS_CLASS, true);
    }

    public function process(Program $program): ?Node
    {
        if ($this->tokenManager->getNextTokenAfterCurrent()->isSymbol()) {
            return null;
        }
        $node = new PropertyDefinition($this->tokenManager->getCurrentToken());
        $node->modifiers[] = (new ModifiersTransform($this->tokenManager))->map($this->tokenManager->getCurrentToken());

        $node = $this->parsePropertyWithTypes($node);
        $this->parseContext->variables->addProperty($node);
        return $node;
    }

    private function parsePropertyWithTypes(PropertyDefinition $node): PropertyDefinition
    {
        $types = [];

        while (!$this->tokenManager->isEndOfTokens()) {
            $token = $this->tokenManager->getCurrentToken();

            if ($token->isType() || $this->isTypeFormat($token)) {
                $types[] = $token->value;
            }

            $nextToken = $this->tokenManager->getNextTokenAfterCurrent();

            $this->tokenManager->advance();

            if ($nextToken->isIdentifier()) {
                $node->name = trim((string) $nextToken->value);
                break;
            }
        }

        $node->type = implode('|', $types);
        return $node;
    }

    private function isTypeFormat(Token $token): bool
    {
        if ($token->type !== 'T_IDENTIFIER') {
            return false;
        }
        $value = $token->value;
        $firstLetter = mb_substr((string) $value, 0, 1);
        return $firstLetter === mb_strtoupper($firstLetter);
    }
}
