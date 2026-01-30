<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\StringNode;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\GlobalFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Helper\Debug\Debug;

class StringCastValue extends GlobalFactory
{
    use DataArrayObjectModelingTrait;
    use DataParamsModelingTrait;

    public function isTheCase(Token $token, ParseContext $parseContext): bool
    {
        return $token->isType() &&
            $token->value === 'String';
    }

    public function process(Token $token, ParseContext $parseContext): ?Node
    {

        $parseContext->tokenManager->advance();
        // Se usa isso ferra por que getArgs tenta o modelo antigo de factories
        $value = current($this->getArgs('casting'))->value;
        Debug::show($value);
        exit;
        return new StringNode($token, (string) "'" . $value . "'");

        // Se usa isso loop
        $parseContext->tokenManager->advance();
        $value = $parseContext->emitter->emit($token, $parseContext);
        return new StringNode($token, (string) "'" . $value . "'");
    }
}
