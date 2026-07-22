<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Declarations;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\IdentifierResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\AttributeUsageNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamsNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * Parses the application of an attribute: @Entity('User') or @Field(name: 'x', type: 'String')
 *
 * Token sequence after entering this context:
 *   Entity  →  handle() receives identifier → fills node->name
 *   (       →  opens ParamsConsumptionContext
 *   args... →  handled by ParamsConsumptionContext
 *   )       →  closes ParamsConsumptionContext
 *   EOL     →  canClose() → true → afterClose() stores node in definePrevious
 *
 * @extends AbstractContext<AttributeUsageNode>
 */
class AttributeUsageContext extends AbstractContext
{
    public function __construct(AttributeUsageNode $node)
    {
        parent::__construct($node);
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        if (($token->isIdentifier() || $token->isGlobalConst()) && $this->node->name === '') {
            $this->node->name = \is_string($token->value) ? $token->value : '';
            return null;
        }

        if ($token->isOpeningParenthesis() && $parseContext->contextManager !== null) {
            $paramsNode = new ParamsNode($token);
            $parseContext->contextManager->enter(
                new ParamsConsumptionContext($paramsNode)
            );
            $this->addChild($paramsNode);
            return null;
        }

        if ($token->isEndOfLine() || $token->isComment()) {
            return null;
        }

        throw new CompileException(
            $token->value . ' is not supported in attribute usage context!',
            $token->line,
            $token->column,
        );
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine();
    }

    public function onClose(Token $token, ParseContext $parseContext): void
    {
        $paramsNode = $this->children[0] ?? null;
        if ($paramsNode instanceof ParamsNode) {
            $this->node->params = $paramsNode;
        }
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        $parseContext->pendingAttributes[] = $this->node;
    }
}
