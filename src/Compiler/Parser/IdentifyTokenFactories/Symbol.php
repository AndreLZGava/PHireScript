<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\ArrayCastVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\BlockBracketsCommaOnMethod;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\BlockBrackets;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\BlockParenthesisOnMethod;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\BoolCastVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\BoolLiteralVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\CharactersOnMethods;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\ComplexObject;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\DotOnGeneral;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\FloatCastVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\GetterAndSetters;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\GettingArguments;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\IntCastVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\NumberLiteralVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\ObjectArrayLiteralVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\ObjectCastVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\SingleCommaOnClass;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\SingleOpenParenthesisOperator;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\StringCastVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\StringLiteralVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\VariableAssignmentFactory;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Symbols\VariableLiteralVariable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataArrayObjectModelingTrait;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Traits\DataParamsModelingTrait;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class Symbol extends GlobalFactory
{
    public Program $program;
    public ParseContext $parseContext;

    public function process(Program $program, ParseContext $parseContext): ?Node
    {
        $this->program = $program;
        $this->parseContext = $parseContext;

        $factories = [
            new ArrayCastVariable($this->tokenManager),
            new BlockBrackets($this->tokenManager),
            new BlockBracketsCommaOnMethod($this->tokenManager),
            new BlockParenthesisOnMethod($this->tokenManager),
            new BoolCastVariable($this->tokenManager),
            new BoolLiteralVariable($this->tokenManager),
            new CharactersOnMethods($this->tokenManager),
            new ComplexObject($this->tokenManager),
            new DotOnGeneral($this->tokenManager),
            new FloatCastVariable($this->tokenManager),
            new GetterAndSetters($this->tokenManager),
            new GettingArguments($this->tokenManager),
            new IntCastVariable($this->tokenManager),
            new NumberLiteralVariable($this->tokenManager),
            new ObjectArrayLiteralVariable($this->tokenManager),
            new ObjectCastVariable($this->tokenManager),
            new SingleCommaOnClass($this->tokenManager),
            new SingleOpenParenthesisOperator($this->tokenManager),
            new StringCastVariable($this->tokenManager),
            new StringLiteralVariable($this->tokenManager),
            new VariableLiteralVariable($this->tokenManager),
            new VariableAssignmentFactory($this->tokenManager),
        ];

        foreach ($factories as $parser) {
            if ($parser->isTheCase()) {
                return $parser->process($program, $parseContext);
            }
        }

        Debug::show($this->tokenManager->getLeftTokens());
        exit;

        Debug::show(
            [
                'currentToken' => $this->tokenManager->getCurrentToken(),
                'context' => $this->tokenManager->getContext(),
                'program' => $program
            ],
            debug_backtrace(2)
        );
        exit;
    }
}
