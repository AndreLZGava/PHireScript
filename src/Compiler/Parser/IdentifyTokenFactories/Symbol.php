<?php

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\AssignmentNode;
use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\MethodDefinition;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Parser\Ast\NullExpressionNode;
use PHPScript\Compiler\Parser\Ast\PropertyAccessNode;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;
use PHPScript\Compiler\Parser\Ast\ReturnNode;
use PHPScript\Compiler\Parser\Ast\ThisExpressionNode;
use PHPScript\Compiler\Parser\Ast\VariableNode;
use PHPScript\Compiler\Parser\Ast\VoidExpressionNode;
use PHPScript\Compiler\Parser\Transformers\ModifiersTransform;
use PHPScript\Compiler\Program;
use PHPScript\Helper\Debug\Debug;
use PHPScript\Runtime\RuntimeClass;
use SebastianBergmann\Environment\Runtime;

class Symbol extends GlobalFactory
{
    public function process(Program $program): ?Node
    {
        $currentToken = $this->tokenManager->getCurrentToken();
        $currentContext  = $this->tokenManager->getContext();

        if (
             in_array($currentToken['value'], ['.'])
             && $currentContext === 'general'
        ) {
            // Getting pkg name probably;
            return null;
        }

        if (
            in_array($currentToken['value'], RuntimeClass::START_END_ARGUMENTS)
            && $currentContext === RuntimeClass::CONTEXT_GET_ARGUMENTS
        ) {
            // Ignore () for getting arguments
            return null;
        }

        if (
            in_array($currentToken['value'], ['[', ']', ','])
            && $currentContext === 'method'
        ) {
            // Ignore for methods
            $node = new GlobalStatement();
            $node->code = $currentToken['value'];
            return $node;
        }

        if (in_array($currentToken['value'], RuntimeClass::BLOCK_DELIMITERS)) {
            return null;
        }

        if (
            in_array($currentToken['value'], RuntimeClass::CHARACTERS_ON_METHODS) &&
            in_array($currentContext, RuntimeClass::OBJECT_AS_CLASS)
        ) {
            return null;
        }

        if (
            in_array($currentToken['value'], RuntimeClass::GETTER_AND_SETTER) &&
            in_array($currentContext, RuntimeClass::OBJECT_AS_CLASS)
        ) {
            $node = new MethodDefinition();
            return $this->parseGetterAndSetter($node);
        }

        if (
            in_array($currentToken['value'], RuntimeClass::ACCESSORS) &&
            in_array($currentContext, RuntimeClass::OBJECT_AS_CLASS)
        ) {
            //Debug::show($this->tokenManager->getNextTokenAfterCurrent());exit;
            if ($this->tokenManager->getNextTokenAfterCurrent()['type'] === 'T_SYMBOL') {
                return null;
            }
            $node = new PropertyDefinition();
            $node->line = $currentToken['line'];
            $node->modifiers[] = (new ModifiersTransform($this->tokenManager))->map($currentToken);
            return $this->parsePropertyWithTypes($node);
        }
        Debug::show(
            [
            'currentToken' => $currentToken,
            'context' => $currentContext,
            'program' => $program],
            debug_backtrace(2)
        );
        exit;
        return null;
    }

    private function parseGetterAndSetter(MethodDefinition $node)
    {
        $tokens = $this->tokenManager->getLeftTokens();
        $previous = $this->tokenManager->getPreviousTokenBeforeCurrent();
        $currentToken = $this->tokenManager->getCurrentToken();
        $node->modifiers[] = (new ModifiersTransform($this->tokenManager))->map($previous);
        $toWalk = 0;
        if (
            $previous['type'] === 'T_EOL' ||
            $previous['type'] === 'T_COMMENT'
        ) {
            $toWalk = 1;
        }

        $types = [];
        $name = 'wrongCompilation';
        $typeMethod = '';
        $processBeforeAttribution = true;
        $defaultValue = null;
        foreach ($tokens as $key => $token) {
            if ($processBeforeAttribution && $token['type'] === 'T_TYPE') {
                $types[] = $token['value'];
            }

            if ($processBeforeAttribution && $token['type'] === 'T_IDENTIFIER') {
                $name = trim($token['value']);
            }

            if ($token['type'] === 'T_SYMBOL' && $token['value'] === '=') {
                $processBeforeAttribution = false;
                $defaultValue = $tokens[$key + 1];
            }

            if ($token['type'] === 'T_EOL') {
                break;
            }
        }

        $node->line = $currentToken['line'];
        if ($currentToken['value'] === '>') {
            $typeMethod = 'set';
            $arg = new PropertyDefinition();
            $arg->line = $currentToken['line'];
            $arg->name = $name;
            $arg->type = implode("|", $types);
            if ($defaultValue) {
                $arg->defaultValue = $defaultValue;
            }
            $node->args[] = $arg;
            $property = new PropertyAccessNode(
                new ThisExpressionNode(),
                $name
            );
            $assignment = new AssignmentNode($property, new VariableNode($name));
            $node->bodyCode[] = $assignment;
            $returnStatement = new ReturnNode(new VoidExpressionNode());
            $node->bodyCode[] = $returnStatement;
            $node->returnType = 'Void';
        }

        if ($currentToken['value'] === '<') {
            $typeMethod = 'get';
            $node->args = [];
            $property = new PropertyAccessNode(
                new ThisExpressionNode(),
                $name
            );
            $returnStatement = new ReturnNode($property);
            $node->bodyCode[] = $returnStatement;

            $node->returnType = implode("|", $types);
        }

        $node->name = $typeMethod . ucfirst($name);
        // $this->tokenManager->walk($toWalk);
        return $node;
    }

    private function parsePropertyWithTypes(PropertyDefinition $node): PropertyDefinition
    {
        $types = [];

        while (!$this->tokenManager->isEndOfTokens()) {
            $token = $this->tokenManager->getCurrentToken();

            if ($token['type'] === 'T_TYPE' || $this->isTypeFormat($token)) {
                $types[] = $token['value'];
            }

            $nextToken = $this->tokenManager->getNextTokenAfterCurrent();

            $this->tokenManager->advance();

            if ($nextToken['type'] === 'T_IDENTIFIER') {
                $node->name = trim($nextToken['value']);
                break;
            }
        }

        $node->type = implode('|', $types);
        return $node;
    }

    private function isTypeFormat(array $token): bool
    {
        if ($token['type'] !== 'T_IDENTIFIER') {
            return false;
        }
        $value = $token['value'];
        $firstLetter = mb_substr($value, 0, 1);
        return $firstLetter === mb_strtoupper($firstLetter);
    }
}
