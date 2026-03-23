<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Root;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class ProgramChecker implements Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof Program;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        foreach ($checker->checkers as $check) {
            foreach ($node->statements as $statements) {
                if ($check->mustCheck($statements)) {
                    $check->check($statements, $checker);
                }
            }
        }
    }
}
