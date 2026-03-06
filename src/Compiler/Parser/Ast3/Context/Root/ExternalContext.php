<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast3\Context\Root;

use PHireScript\Compiler\Parser\Ast3\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\CommaResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Expressions\Types\TypeResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\BackSlashResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\ClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\DotResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\IdentifierResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Root\OpeningCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast3\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\ExternalNode;
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
    private bool $alreadyEnteredGroup = false;
    private array $dependencies = [];
    private string $dependency = '';

    public function __construct(ExternalNode $node)
    {
        parent::__construct($node);
        $this->resolvers = [
            new IdentifierResolver(),
            new TypeResolver(),
            new BackSlashResolver(),
            new EndOfLineResolver(),
            new CommaResolver(),
            new OpeningCurlyBracketResolver(),
            new ClosingCurlyBracketResolver(),
        ];
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = get_class($resolver);
                $resolver->resolve($token, $parseContext, $this);
                $this->handleMultiplePackages($token);

                return null;
            }
        }
        Debug::show($token);exit;
        throw new CompileException(
            $token->value . ' is not supported in external definition context!',
            $token->line,
            $token->column,
        );
    }

    private function handleMultiplePackages(Token $token): void
    {
        if ($token->value === '}' || $token->isEndOfLine()) {
            return;
        }

        if ($token->value === '{') {
            $this->alreadyEnteredGroup = true;
            return;
        }

        if ($this->alreadyEnteredGroup && isset($this->getChildrenValues())) {
            $this->dependencies[] = $this->getChildrenValues();
            $newPackage = [];
            foreach ($this->dependencies as $dependency) {
                $newPackage[] = $this->dependency . $dependency;
            }

            $this->node->namespaces = $newPackage;
            $this->children = [];
            return;
        }

        $this->dependency .= $this->getChildrenValues() ?? '';
        $this->node->namespaces = [$this->dependency];
        $this->children = [];
        return;
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        return $token->isEndOfLine();
    }
}
