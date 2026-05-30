<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Root;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\CommaResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\TypeResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\BackSlashResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\ClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\External\ExternalAliasResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\External\ExternalConstNameResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\External\GroupUseResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\IdentifierResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ExternalNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\GroupUseNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\NamespaceNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ExternalNode>
 */
class ExternalContext extends AbstractContext
{
    public bool $collectingAlias = false;

    private readonly array $resolvers;

    public function __construct(ExternalNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new ExternalAliasResolver(),
            new ExternalConstNameResolver(),
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
                $token->processedBy = $resolver::class;
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
        $alias   = null;
        $namespaces = [];
        $hasGroup = false;

        $seenAs = false;
        foreach ($this->children as $item) {
            if (\is_string($item)) {
                if ($item === '__AS__') {
                    $seenAs = true;
                    continue;
                }
                if ($seenAs) {
                    $alias = $item;
                } else {
                    $package .= $item;
                }
                continue;
            }

            if ($item instanceof GroupUseNode) {
                foreach ($item->parts as $itemAlias => $part) {
                    $hasGroup = true;
                    $packageNode = new NamespaceNode($token);
                    $packageNode->package = $package . $part;
                    $packageNode->alias = \is_string($itemAlias) ? $itemAlias : null;
                    $namespaces[] = $packageNode;
                }
            }
        }

        if (!$hasGroup) {
            $packageNode = new NamespaceNode($token);
            $packageNode->namespace = $package;
            $packageNode->alias     = $alias;
            $namespaces[] = $packageNode;
            $parseContext->registerExternalAlias($alias ?? $package, $package);
        }

        $this->node->namespaces = $namespaces;
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine();
    }
}
