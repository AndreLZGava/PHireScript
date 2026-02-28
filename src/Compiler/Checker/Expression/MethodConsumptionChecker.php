<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Expression;

use Exception;
use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\Parser\Ast\FunctionNode;
use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\Ast\QueueNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\Exceptions\CompileException;

class MethodConsumptionChecker implements Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof FunctionNode;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        $type = $node->variableBase?->type?->getRawType();
        $variableTypes = $node->variableBase?->type?->types ?? [];
        $params = $node->params->params;
        foreach ($params as $param) {
            $paramRawType = $param->getRawType();
            if (!empty($variableTypes) && !in_array($paramRawType, $variableTypes)) {
                throw new CompileException(
                    'Param of value ' . $paramRawType .
                        ' not allowed for ' . $type .
                        '. Allowed in this case ' . implode($variableTypes) . '!',
                    $param->token->line,
                    $param->token->column
                );
            }
        }
        return;
    }
}
