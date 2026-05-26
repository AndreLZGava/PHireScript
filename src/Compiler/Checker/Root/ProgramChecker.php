<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Root;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\AssignmentNode;
use PHireScript\Compiler\Program;

#[CompilerPass(order: 4)]
class ProgramChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof Program;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        $this->willCheck($node->statements, $checker);

        $values = [];
        foreach ($node->statements as $statement) {
            if ($statement instanceof AssignmentNode && $statement->right !== null) {
                $values[] = $statement->right;
            }
        }
        if ($values !== []) {
            $this->willCheck($values, $checker);
        }
    }
}
