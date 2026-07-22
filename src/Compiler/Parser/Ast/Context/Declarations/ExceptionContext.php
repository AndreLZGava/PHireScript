<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Context\Declarations;

use PHireScript\Compiler\Parser\Ast\Context\AbstractContext;
use PHireScript\Compiler\Parser\Ast\Resolver\Declaration\PropertyResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Expressions\Types\ClosingCurlyBracketResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\Class\ExtendsResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Root\IdentifierResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\CommentResolver;
use PHireScript\Compiler\Parser\Ast\Resolver\Statements\EndOfLineResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ExceptionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;

/**
 * @extends AbstractContext<ExceptionNode>
 */
class ExceptionContext extends AbstractContext
{
    private bool $insideBody = false;
    private bool $consumingMessage = false;

    public function __construct(ExceptionNode $node)
    {
        parent::__construct($node);
    }

    public function handle(Token $token, ParseContext $parseContext): ?Node
    {
        if ($token->isEndOfLine() || $token->isComment()) {
            return null;
        }

        // Name resolution — first identifier after 'exception' keyword
        if (!$this->insideBody && empty($this->node->name) && $token->isIdentifier()) {
            $this->node->name = $token->value;
            return null;
        }

        // extends clause
        if (!$this->insideBody && $token->value === 'extends') {
            $resolver = new ExtendsResolver();
            $resolver->resolve($token, $parseContext, $this);
            return null;
        }

        // ExtendsNode child arrives via addChild — capture it on the node
        // (handled in addChild override below)

        // Opening body brace
        if (!$this->insideBody && $token->isOpeningCurlyBracket()) {
            $this->insideBody = true;
            return null;
        }

        if ($this->insideBody) {
            // Closing brace ends the body
            if ($token->isClosingCurlyBracket()) {
                $parseContext->contextManager->exit();
                return null;
            }

            // message: 'template string'
            if ($token->isIdentifier() && $token->value === 'message') {
                $this->consumingMessage = true;
                return null;
            }

            if ($this->consumingMessage) {
                if ($token->isColon()) {
                    return null;
                }
                if ($token->isStringLiteral()) {
                    $this->node->messageTemplate = \trim($token->value, '"\'');
                    $this->consumingMessage = false;
                    return null;
                }
            }

            // constructor block — mark and skip its body
            if ($token->isKeyword() && $token->value === 'constructor') {
                $this->node->hasCustomConstructor = true;
                $this->skipBlock($parseContext);
                return null;
            }

            // Property declaration (TypeName propName)
            $propertyResolver = new PropertyResolver();
            if ($propertyResolver->isTheCase($token, $parseContext, $this)) {
                $token->processedBy = $propertyResolver::class;
                $propertyResolver->resolve($token, $parseContext, $this);
                return null;
            }
        }

        return null;
    }

    public function addChild($child): void
    {
        if ($child instanceof \PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassExtendsNode) {
            $this->node->extends = $child;
            return;
        }
        if ($child instanceof PropertyNode) {
            $this->node->properties[] = $child;
            return;
        }
        parent::addChild($child);
    }

    public function canClose(Token $token, ParseContext $parseContext): bool
    {
        // Close when encountering EOL/EOF with no body, or handled via closing brace above
        return !$this->insideBody && $token->isEndOfLine();
    }

    /**
     * Skip over a constructor { } block using peek-only look-ahead, then advance past it.
     * Only Parser.php may advance() — so we enter a sub-context that consumes tokens until depth 0.
     */
    private function skipBlock(ParseContext $parseContext): void
    {
        $parseContext->contextManager->enter(
            new ExceptionConstructorSkipContext()
        );
    }
}
