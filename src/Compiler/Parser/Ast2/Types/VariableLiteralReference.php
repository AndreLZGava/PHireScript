<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast2\Types;

use Exception;
use PHireScript\Compiler\Parser\Ast2\GlobalFactory;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class VariableLiteralReference extends GlobalFactory {
    public function isTheCase(Token $token, ParseContext $parseContext): bool {
        return $token->isIdentifier() &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->isEndOfLine();
    }

    public function process(Token $token, ParseContext $parseContext): ?Node {
        if (empty($parseContext->variables->getVariable($token->value))) {
            throw new Exception("Variable {$token->value} is not defined yet!");
        }

        $variableReference = new VariableReferenceNode(
            token: $token,
            name: $token->value,
            value: $token->value,
            type: null,
        );

        $current = $parseContext->context->getCurrentContextElement();
        if ($current instanceof AssignmentNode) {
            $current->right = $variableReference;
            $current->left->type = $variableReference;
        }

        return null;
    }
}
