<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Statements;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Context\Declarations\ParamsConsumptionContext;
use PHireScript\Compiler\Parser\Ast\Nodes\Expressions\ExceptionCallNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\ThrowStatementNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamsNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;

/**
 * Parses: throw ExceptionType(namedArg: value, ...)
 * @extends AbstractContext<ThrowStatementNode>
 */
class ThrowContext extends AbstractContext
{
    private ?string $typeName = null;
    private bool $argsConsumed = false;
    private ?Token $throwToken;

    public function __construct(ThrowStatementNode $node, Token $throwToken)
    {
        parent::__construct($node);
        $this->throwToken = $throwToken;
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        if ($token->isComment()) {
            return null;
        }

        if ($token->isEndOfLine()) {
            return null;
        }

        // Step 1: capture exception type name
        if ($this->typeName === null && ($token->isIdentifier() || $token->isConst())) {
            $this->typeName = $token->value;
            return null;
        }

        // Step 2: opening paren → delegate to ParamsConsumptionContext
        if ($this->typeName !== null && !$this->argsConsumed && $token->isOpeningParenthesis()) {
            $paramsNode = new ParamsNode($token);
            $parseContext->contextManager->enter(new ParamsConsumptionContext($paramsNode));
            $this->addChild($paramsNode);
            return null;
        }

        return null;
    }

    public function addChild($child): void
    {
        if ($child instanceof ParamsNode) {
            $this->argsConsumed = true;
        }
        parent::addChild($child);
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        // Close after the args are consumed and we see an EOL or end-of-statement
        return $this->argsConsumed && ($token->isEndOfLine() || $token->isClosingCurlyBracket());
    }

    public function onClose(Token $token, ParseContext $parseContext): void
    {
        $paramsNode = $this->children[0] ?? null;
        $args = $paramsNode instanceof ParamsNode ? ($paramsNode->params ?? []) : [];

        $callNode = new ExceptionCallNode(
            token: $this->throwToken,
            typeName: $this->typeName ?? '',
            args: $args,
        );
        $this->node->exceptionExpression = $callNode;
    }
}
