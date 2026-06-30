<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Resolver\Expressions;

use Exception;
use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Expressions\FunctionCallContext;
use PHireScript\Compiler\Parser\Ast\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\BoolNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\LiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NullNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\NumberNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ObjectLiteralNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\StringNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

// possible scenarios:
// A function returns a string and the next one calls string, consuming the focus variable
// A function returns a string and the next one is an array
// A function returns more than one type
class FunctionCallResolver implements ContextTokenResolver
{
    private bool $assignmentContext = false;

    private function getFocusRawType(mixed $focus): ?string
    {
        if ($focus === null) {
            return null;
        }
        // Try ->type->getRawType() first (VariableDeclarationNode, VariableReferenceNode)
        if (property_exists($focus, 'type') && $focus->type !== null && method_exists($focus->type, 'getRawType')) {
            return $focus->type->getRawType();
        }
        // Fall back to direct getRawType() (StringNode, NumberNode, FunctionNode, etc.)
        if (method_exists($focus, 'getRawType')) {
            return $focus->getRawType();
        }
        return null;
    }

    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool
    {
        try {
            $focus = $parseContext->variables->getVariableOnFocus();
            return $token->isIdentifier() &&
                $parseContext->tokenManager->getNextTokenAfterCurrent()->isOpeningParenthesis() &&
                (
                    $parseContext->symbolTable->getFunctionFromLastExecution($token->value) ||
                    $parseContext->symbolTable->from(
                        $this->getFocusRawType($focus)
                    )->getFunction($token->value)
                );
        } catch (\Exception) {
            Debug::display($parseContext->variables->getVariableOnFocus());
            exit;
        }
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $focus = $parseContext->variables->getVariableOnFocus();
        $variableType = $this->getFocusRawType($focus);
        $functionDefinition = $parseContext->symbolTable->from(
            $variableType
        )->getFunction($token->value);

        $onFocus = $focus;
        $this->assignmentContext = ($context->assignmentContext ?? false) || ($context->returnContext ?? false);

        if (empty($functionDefinition)) {
            $functionDefinition = $parseContext->symbolTable->getFunctionFromLastExecution($token->value, true);
            /**$onFocus = \end($parseContext->program->statements);
            if($onFocus instanceof AssignmentNode) {
                $onFocus = $onFocus->right;
            }
            $onFocus->type = $onFocus;
            $onFocus->processedByOther = false;
            $onFocus->mustProcess = false;
            $onFocus = clone $onFocus;
            $onFocus->mustProcess = true;*/
        }

        if (is_null($functionDefinition)) {
            throw new CompileException(
                'Method ' . $token->value . ' is not defined for variable of type '
                    . $variableType,
                $token->line,
                $token->column
            );
        }

        $function = new FunctionNode(token: $token);
        $function->method = $functionDefinition;
        $function->variableBase = $onFocus;

        $function->isChainLink = $onFocus instanceof FunctionNode;

        $this->overrideVariableOnFocus($function, $functionDefinition, $token);
        $parseContext->variables->setVirtualVariable($function);

        $parseContext->contextManager->enter(
            new FunctionCallContext($function)
        );
        $context->addChild($function);
    }

    private function overrideVariableOnFocus($function, $functionDefinition, $token)
    {
        $function->overrideVariableFocus = \count($functionDefinition->returnOfPhpExecution) > 0 &&
            $functionDefinition->overridesSelfParam &&
            !$this->assignmentContext;

        if ($function->overrideVariableFocus) {
            $firstType = \current($function->method->returnOfPhpExecution);
            $firstType = $firstType == 'Mixed' ? \current($function->variableBase?->type?->types ?? []) : $firstType;
            $newVariable = $this->getNewVirtualVariable($token, $firstType);
            if (property_exists($newVariable, 'types')) {
                $newVariable->types = $function->method->subTypes;
            }
            // Only update value/type on VariableDeclarationNode (left side of assignment)
            // FunctionNode manages its own return type via getRawType(), so skip it
            $isVarDecl = $function->variableBase
                instanceof \PHireScript\Compiler\Parser\Ast\Nodes\Statements\VariableDeclarationNode;
            if ($isVarDecl) {
                $function->variableBase->value = $newVariable;
                $function->variableBase->type = $newVariable;
            } elseif (!($function->variableBase instanceof FunctionNode)) {
                if (property_exists($function->variableBase, 'type')) {
                    $function->variableBase->type = $newVariable;
                }
            }
        }
    }

    private function getNewVirtualVariable($token, $value)
    {
        return match (true) {
            $value === 'Array'  => new ArrayLiteralNode($token),
            $value === 'String' => new StringNode($token, $value),
            $value === 'Int', $value === 'Float' => new NumberNode($token, floatval($value)),
            $value === 'Object' => new ObjectLiteralNode($token, $value),
            $value === 'Bool'   => new BoolNode($token, boolval($value)),
            $value === 'Null'   => new NullNode($token),
            $value === 'Void'   => new NullNode($token),
            $value === 'Mixed'  => new LiteralNode($token, null, 'Mixed'),
            // SuperTypes and custom types — use LiteralNode with the raw type name
            default => new LiteralNode($token, null, $value),
        };
    }
}
