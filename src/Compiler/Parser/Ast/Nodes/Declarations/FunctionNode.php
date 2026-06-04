<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use PHireScript\Compiler\Parser\Ast\Nodes\Expression\Types\Type;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Expression;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamsNode;

class FunctionNode extends Node implements Type
{
    private string $raw = 'Function';
    public mixed $variableBase;
    public BaseMethods $method;
    public ?ParamsNode $params = null;
    public self $type;
    public bool $overrideVariableFocus = false;
    public bool $generateNewVariable = false;
    public bool $isExternalInstantiation = false;
    public bool $isExternalMethodCall = false;
    public string $externalMethodName = '';
    public bool $safeNavigation = false;
    public bool $isChainLink = false;
    public function __construct(public Token $token)
    {
        $this->type = $this;
        return parent::__construct($token);
    }

    public function getRawType(): string
    {
        if (isset($this->method) && !empty($this->method->returnOfPhpExecution)) {
            $type = current($this->method->returnOfPhpExecution);
            if ($type !== 'Void' && $type !== '') {
                return $type;
            }
        }
        return $this->raw;
    }
}
