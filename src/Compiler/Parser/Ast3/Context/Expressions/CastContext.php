<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Expressions;

use Exception;
use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\CastingConsumptionParams\ClosingParenthesisResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\CastingConsumptionParams\OpeningParenthesisResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\BoolLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\NumberLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\StringLiteralResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\TypeResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\VariableReferenceResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\PipeResolver;
use PHireScript\Compiler\Parser\Ast\CastingNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class CastContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(CastingNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new OpeningParenthesisResolver(),
            new ClosingParenthesisResolver(),

            new StringLiteralResolver(),
            new BoolLiteralResolver(),
            new NumberLiteralResolver(),

            new VariableReferenceResolver(),

            new EndOfLineResolver(),
            new CommentResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $param = null;
                if (!is_null($this->children[0]->params)) {
                    $param = $this->children[0]->params[0];
                }
                $this->node->value = $param;

                return null;
            }
        }
        throw new CompileException(
            $token->value . ' is not supported in casting definition context!',
            $token->line,
            $token->column
        );
    }

    public function validation(Token $token, ParseContext $parseContext): void
    {
        if (
            ($token->isEndOfLine() || $token->value === ')') &&
            is_null($this->node->value)
        ) {
            throw new CompileException(
                'Casting value to ' . $this->node->to .
                    ' must receive at least one parameter!',
                $this->node->line,
                $this->node->column
            );
        }
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        $parseContext->contextManager->exit();
        return;
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine() || $token->isComment();
    }
}
