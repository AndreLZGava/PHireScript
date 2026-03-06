<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Resolver\Expressions;

use Exception;
use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Context\Expressions\MethodConsumptionContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\ContextTokenResolver;
use PHireScript\Compiler\Parser\Ast\ArrayLiteralNode;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\BoolNode;
use PHireScript\Compiler\Parser\Ast\FunctionNode;
use PHireScript\Compiler\Parser\Ast\NumberNode;
use PHireScript\Compiler\Parser\Ast\ObjectLiteralNode;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

// possiveis cenários:
// Tenho uma função que retorna string e a proxima chama string, está consumindo a variavel de foco
// Tenho uma função que retorna uma string e a proxima é um array, está
// Tenho função que retorna mais de um tipo
class FunctionCallResolver implements ContextTokenResolver {
    private bool $blockOverrideSelfVariable = false;
    public function isTheCase(Token $token, ParseContext $parseContext, AbstractContext $context): bool {
        if (
            $token->isIdentifier() &&
            $parseContext->tokenManager->getNextTokenAfterCurrent()->value === '('
        ) {
            //   Debug::show($parseContext->variables->getVariableOnFocus());
        }
        try {
            return $token->isIdentifier() &&
                $parseContext->tokenManager->getNextTokenAfterCurrent()->value === '(' &&
                (
                    $parseContext->symbolTable->getFunctionFromLastExecution($token->value) ||
                    $parseContext->symbolTable->from(
                        $parseContext->variables->getVariableOnFocus()?->type?->getRawType()
                    )->getFunction($token->value)

                );
        } catch (\Exception $e) {
            Debug::display($parseContext->variables->getVariableOnFocus());
            exit;
        }
    }

    public function resolve(
        Token $token,
        ParseContext $parseContext,
        AbstractContext $context
    ): void {
        $variableType = $parseContext->variables->getVariableOnFocus()?->type?->getRawType();
        $functionDefinition = $parseContext->symbolTable->from(
            $variableType
            )->getFunction($token->value);

            $onFocus = $parseContext->variables->getVariableOnFocus();

            if (empty($functionDefinition)) {
            $this->blockOverrideSelfVariable = $context->blockOverrideSelfVariable ?? false;
            $functionDefinition = $parseContext->symbolTable->getFunctionFromLastExecution($token->value, true);
            /**$onFocus = end($parseContext->program->statements);
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

        $this->overrideVariableOnFocus($function, $functionDefinition, $token);
        //$parseContext->variables->setVirtualVariable($function);

        $parseContext->contextManager->enter(
            new MethodConsumptionContext($function)
        );
        $context->addChild($function);
    }

    private function overrideVariableOnFocus($function, $functionDefinition, $token) {
        $function->overrideVariableFocus = count($functionDefinition->returnOfPhpExecution) > 0 && $this->blockOverrideSelfVariable;
        if ($function->overrideVariableFocus) {
            $firstType = current($function->method->returnOfPhpExecution);
            $firstType = $firstType == 'Mixed' ? current($function->variableBase?->type?->types ?? []) : $firstType;
            $newVariable = $this->getNewVirtualVariable($token, $firstType);
            if (property_exists($newVariable, 'types')) {
                $newVariable->types = $function->method->subTypes;
            }
            $function->variableBase->value = $newVariable;
            $function->variableBase->type = $newVariable;
        }
    }

    private function getNewVirtualVariable($token, $value) {
        return match (true) {
            $value === 'Array'  => new ArrayLiteralNode($token),
            $value === 'String' => new StringNode($token, $value),

            $value === 'Int', $value === 'Float' => new NumberNode($token, floatval($value)),

            $value === 'Object' => new ObjectLiteralNode($token, $value),
            $value === 'Bool'   => new BoolNode($token, boolval($value)),
            default => throw new CompileException('Type not supported: ' . $value, $token->line, $token->column),
        };
    }
}
