<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Declarations;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\IdentifierResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\AssignmentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\PropertyNode;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class PropertyDeclarationContext extends AbstractContext
{
    private array $resolvers = [];

    public function __construct(PropertyNode $node)
    {
        parent::__construct($node);

        $this->resolvers = [
            new IdentifierResolver(),
            new EndOfLineResolver(),
            new AssignmentResolver(),
            new CommentResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->processProperty();
                return null;
            }
        }
        throw new CompileException(
            $token->value . ' is not supported in property declaration context!',
            $token->line,
            $token->column
        );
    }

    private function processProperty()
    {
        $this->node->name = $this->children[0];
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine();
    }
}
