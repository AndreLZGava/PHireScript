<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Root;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\CommaResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\TypeResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\BackSlashResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\ClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\DotResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\External\GroupUseResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\IdentifierResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\OpeningCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\ExternalNode;
use PHireScript\Compiler\Parser\Ast\GroupUseNode;
use PHireScript\Compiler\Parser\Ast\NamespaceNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\UseNode;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ParamsNode>
 */
class ExternalContext extends AbstractContext
{
    private array $resolvers;

    public function __construct(ExternalNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new IdentifierResolver(),
            new TypeResolver(),
            new BackSlashResolver(),
            new EndOfLineResolver(),
            new CommaResolver(),
            new GroupUseResolver(),
            new ClosingCurlyBracketResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);

                return null;
            }
        }
        throw new CompileException(
            $token->value . ' is not supported in external definition context!',
            $token->line,
            $token->column,
        );
    }

    public function afterClose(Token $token, ParseContext $parseContext): void
    {
        $package = '';
        $namespaces = [];
        $hasGroup = false;
        foreach ($this->children as $item) {
            if (is_string($item)) {
                $package .= $item;
                continue;
            }

            if ($item instanceof GroupUseNode) {
                foreach ($item->parts as $alias => $part) {
                    $hasGroup = true;
                    $packageNode = new NamespaceNode($token);
                    $packageNode->package = $package . $part;
                    $packageNode->alias = is_string($alias) ? $alias : null;
                    $namespaces[] = $packageNode;
                }
            }
        }
        if (!$hasGroup) {
            $packageNode = new NamespaceNode($token);
            $packageNode->namespace = $package;
            $namespaces[] = $packageNode;
        }

        $this->node->namespaces = $namespaces;
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine();
    }
}
